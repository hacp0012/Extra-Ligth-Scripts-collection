#Requires AutoHotkey v2.0
#NoTrayIcon
#SingleInstance force

; #HotIf WinActive("ahk_exe Code.exe")

; --- Gestion propre du CapsLock pour éviter les toggles intempestifs ---
; Empêche l'activation native quand on l'utilise comme modificateur
; *CapsLock:: return

; *CapsLock Up::
; {
;     ; Ne change l'état du CapsLock que s'il a été pressé et relâché SEUL
;     if (A_PriorKey == "CapsLock") {
;         SetCapsLockState(!GetKeyState("CapsLock", "T"))
;     }
; }

; ! OLD SHORTCUTS
;UP
; Navigation avec CapsLock
CapsLock & h:: Send("{Up}")
CapsLock & n:: Send("{Down}")
CapsLock & j:: Send("{Left}")
CapsLock & k:: Send("{Right}")
CapsLock & u:: Send("{Enter}")

; * NEW SHORTCUTS *
; ;UP
; ~CapsLock & u::
; {
;   Send("{Up}")
; }

; ;DOWN
; ~CapsLock & m::
; {
;   Send("{Down}")
; }

; ;LEFT
; ~CapsLock & h::
; {
;   Send("{Left}")
; }

; ;RIGHT
; ~CapsLock & k::
; {
;   Send("{Right}")
; }

; ;ENTER
; ~CapsLock & j::
; {
;   Send("{Enter}")
; }
; -------------------------

;BACKSPACE
;BACKSPACE
CapsLock & i:: Send("{BackSpace}")

; --- VIRTUAL DESKTOPS SYSTEM (VirtualDesktopAccessor.dll) ---

; Chargement de la DLL
global hVirtualDesktopAccessor := DllCall("LoadLibrary", "Str", A_ScriptDir "\VirtualDesktopAccessor.dll", "Ptr")

if !hVirtualDesktopAccessor {
    MsgBox("VirtualDesktopAccessor.dll non trouvée dans :`n" A_ScriptDir, "Erreur DLL", 48)
}

; Récupération des adresses (On teste deux noms possibles pour le saut)
global GoToProc := DllCall("GetProcAddress", "Ptr", hVirtualDesktopAccessor, "AStr", "GoToDesktopNumber", "Ptr")
if !GoToProc
    global GoToProc := DllCall("GetProcAddress", "Ptr", hVirtualDesktopAccessor, "AStr", "SwitchToDesktop", "Ptr")

global GetCurrentProc := DllCall("GetProcAddress", "Ptr", hVirtualDesktopAccessor, "AStr", "GetCurrentDesktopNumber",
    "Ptr")
global MoveProc := DllCall("GetProcAddress", "Ptr", hVirtualDesktopAccessor, "AStr", "MoveWindowToDesktopNumber", "Ptr"
)

/**
 * Saute vers le bureau spécifié.
 * Utilise la DLL en priorité, puis le clavier en secours.
 */
JumpToDesktop(target) {
    if (target < 1) {
        target := 1
    }

    current := GetCurrentDesktop()

    if (target == current) {
        ToolTip("Déjà sur le Bureau " target)
        SetTimer () => ToolTip(), -800
        return
    }

    ; 1. TENTATIVE VIA DLL (Instantané)
    if (GoToProc) {
        DllCall(GoToProc, "Int", target - 1)
        Sleep(100) ; On attend que Windows mette à jour son état

        ; Si ça a marché, on s'arrête
        if (GetCurrentDesktop() == target) {
            ToolTip("Bureau " target)
            SetTimer () => ToolTip(), -1000
            return
        }
    }

    ; 2. TENTATIVE VIA CLAVIER (Secours si la DLL échoue)
    ; On recalcule la position au cas où elle aurait bougé
    current := GetCurrentDesktop()
    diff := target - current
    if (diff == 0)
        return

    key := (diff > 0) ? "^#{Right}" : "^#{Left}"
    loop Abs(diff) {
        Send(key)
        Sleep(60)
    }

    ToolTip("Bureau " target " (Secours)")
    SetTimer () => ToolTip(), -1000
}

/**
 * Déplace la fenêtre active vers le bureau spécifié.
 */
MoveToDesktop(target) {
    if !MoveProc
        return

    hwnd := WinExist("A")
    if !hwnd
        return

    DllCall(MoveProc, "Ptr", hwnd, "Int", target - 1)
    ToolTip("Fenêtre envoyée au Bureau " target)
    SetTimer () => ToolTip(), -1000
}

global GetDesktopCountProc := DllCall("GetProcAddress", "Ptr", hVirtualDesktopAccessor, "AStr", "GetDesktopCount",
    "Ptr")

/**
 * Récupère l'index actuel via la DLL.
 */
GetCurrentDesktop() {
    if (!hVirtualDesktopAccessor || !GetCurrentProc) {
        return 1
    }
    return DllCall(GetCurrentProc, "Int") + 1
}

; --- CHANGEMENT DE BUREAU AU BORD DE L'ECRAN (Raw Input) ---
global EdgeInputGui := 0
global EdgeSwitchThreshold := 500
global EdgeSwitchAccumulated := 0
global EdgeSwitchDirection := 0
global EdgeScreenLeft := 0
global EdgeScreenRight := 0

InitializeEdgeDesktopSwitch() {
    global EdgeInputGui, EdgeScreenLeft, EdgeScreenRight

    EdgeInputGui := Gui()
    EdgeInputGui.Show("Hide")
    OnMessage(0x00FF, HandleRawInput)

    ; Usage page 1 / usage 2 = souris; RIDEV_INPUTSINK capte aussi en arriere-plan.
    rawDevice := Buffer(A_PtrSize = 8 ? 16 : 12, 0)
    NumPut("UShort", 1, rawDevice, 0)
    NumPut("UShort", 2, rawDevice, 2)
    NumPut("UInt", 0x00000100, rawDevice, 4)
    NumPut("Ptr", EdgeInputGui.Hwnd, rawDevice, 8)

    if !DllCall("RegisterRawInputDevices", "Ptr", rawDevice, "UInt", 1, "UInt", rawDevice.Size, "Int") {
        MsgBox("Impossible d'activer la capture Raw Input pour le changement de bureau.", "Erreur Raw Input", 48)
        return
    }

    EdgeScreenLeft := DllCall("GetSystemMetrics", "Int", 76, "Int")
    screenWidth := DllCall("GetSystemMetrics", "Int", 78, "Int")
    EdgeScreenRight := EdgeScreenLeft + screenWidth - 1
}

HandleRawInput(wParam, lParam, msg, hwnd) {
    global EdgeSwitchThreshold, EdgeSwitchAccumulated, EdgeSwitchDirection
    global EdgeScreenLeft, EdgeScreenRight

    headerSize := A_PtrSize = 8 ? 24 : 16
    dataSize := 0
    result := DllCall("GetRawInputData", "Ptr", lParam, "UInt", 0x10000003, "Ptr", 0,
        "UInt*", &dataSize, "UInt", headerSize, "UInt")

    if (result != 0xFFFFFFFF && dataSize >= headerSize + 20) {
        rawInput := Buffer(dataSize, 0)
        bytesRead := DllCall("GetRawInputData", "Ptr", lParam, "UInt", 0x10000003, "Ptr", rawInput,
            "UInt*", &dataSize, "UInt", headerSize, "UInt")

        if (bytesRead >= headerSize + 20 && NumGet(rawInput, 0, "UInt") == 0) {
            mouseFlags := NumGet(rawInput, headerSize, "UShort")
            if !(mouseFlags & 1) {
                deltaX := NumGet(rawInput, headerSize + 12, "Int")
                if deltaX {
                    point := Buffer(8, 0)
                    if DllCall("GetCursorPos", "Ptr", point) {
                        cursorX := NumGet(point, 0, "Int")
                        direction := 0
                        if (cursorX <= EdgeScreenLeft + 1 && deltaX < 0)
                            direction := -1
                        else if (cursorX >= EdgeScreenRight - 1 && deltaX > 0)
                            direction := 1

                        if direction {
                            if (direction != EdgeSwitchDirection) {
                                EdgeSwitchDirection := direction
                                EdgeSwitchAccumulated := 0
                            }
                            EdgeSwitchAccumulated += Abs(deltaX)

                            if (EdgeSwitchAccumulated >= EdgeSwitchThreshold) {
                                Send(direction < 0 ? "^#{Left}" : "^#{Right}")
                                EdgeSwitchAccumulated := 0
                            }
                        } else {
                            EdgeSwitchDirection := 0
                            EdgeSwitchAccumulated := 0
                        }
                    }
                }
            }
        }
    }

    return DllCall("DefWindowProcW", "Ptr", hwnd, "UInt", msg, "Ptr", wParam, "Ptr", lParam, "Ptr")
}

InitializeEdgeDesktopSwitch()

; --- RACCOURCIS CAPS LOCK ---
; Cette règle laisse CapsLock s'activer normalement, mais annule le changement si on l'a utilisé avec une autre touche.
~*CapsLock::
{
    KeyWait("CapsLock")
    if (A_PriorKey != "CapsLock") {
        SetCapsLockState(!GetKeyState("CapsLock", "T"))
    }
}

#HotIf GetKeyState("CapsLock", "P")

; Saut direct (1-9)
1:: JumpToDesktop(1)
2:: JumpToDesktop(2)
3:: JumpToDesktop(3)
4:: JumpToDesktop(4)
5:: JumpToDesktop(5)
6:: JumpToDesktop(6)
7:: JumpToDesktop(7)
8:: JumpToDesktop(8)
9:: JumpToDesktop(9)

; Déplacer la fenêtre (Alt + 1-9)
!1:: MoveToDesktop(1)
!2:: MoveToDesktop(2)
!3:: MoveToDesktop(3)
!4:: MoveToDesktop(4)
!5:: MoveToDesktop(5)
!6:: MoveToDesktop(6)
!7:: MoveToDesktop(7)
!8:: MoveToDesktop(8)
!9:: MoveToDesktop(9)

; Navigation relative
-:: Send("^#{Right}")
0:: Send("^#{Left}")
; -:: JumpToDesktop(GetCurrentDesktop() + 1)
; 0:: JumpToDesktop(GetCurrentDesktop() - 1)

#HotIf ; --- FIN DES RACCOURCIS CAPS LOCK ---

; ---------------------------------------------------------------------------
;CHANGE TAB
; --- Autres raccourcis ---
CapsLock & w:: Send("^{Tab}")
CapsLock & s:: Send("^s")
CapsLock & q:: Send("^w")

; #HotIf

; ==============================================================================
; MENU DES BUREAUX (CapsLock + Clic Droit sur la barre de titre)
; ==============================================================================
CapsLock & RButton::
{
    CoordMode("Mouse", "Screen")
    MouseGetPos(&mX, &mY, &hWnd)

    hitTest := 0
    try {
        ; WM_NCHITTEST (0x84) pour vérifier si la souris est sur la barre de titre (HTCAPTION = 2)
        hitTest := SendMessage(0x84, 0, (mY << 16) | (mX & 0xFFFF), , "ahk_id " hWnd)
    }

    WinGetPos(&winX, &winY, &winW, &winH, "ahk_id " hWnd)

    ; Vérifie si c'est la barre de titre standard (2)
    ; OU (fallback) si on est dans les 40 premiers pixels en haut (pour les barres de titre personnalisées comme Chrome/VSCode)
    if (hitTest == 2 || (mY >= winY && mY <= winY + 40 && mX >= winX && mX <= winX + winW)) {
        deskCount := 9 ; Valeur par défaut
        if (GetDesktopCountProc) {
            count := DllCall(GetDesktopCountProc, "Int")
            if (count > 0)
                deskCount := count
        }

        DestMenu := Menu()
        loop deskCount {
            DestMenu.Add("Bureau " A_Index, MenuMoveToDesktop.Bind(hWnd, A_Index))
        }
        DestMenu.Show()
    } else {
        ; Si on n'est pas sur la barre de titre, on laisse passer le clic droit normal
        Click("Right")
    }
}

MenuMoveToDesktop(hWnd, target, itemName, itemPos, MyMenu) {
    if !MoveProc
        return
    DllCall(MoveProc, "Ptr", hWnd, "Int", target - 1)
    ToolTip("Fenêtre envoyée au Bureau " target)
    SetTimer(() => ToolTip(), -1000)
}
