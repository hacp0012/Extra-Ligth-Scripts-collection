#Requires AutoHotkey v2.0
#NoTrayIcon
#SingleInstance Force

global moveState := 0

~!LButton::
{
    CoordMode("Mouse", "Screen")
    MouseGetPos(&startX, &startY, &hwnd)

    if (!hwnd || WinGetClass("ahk_id " hwnd) == "WorkerW")
        return
    if (WinGetMinMax("ahk_id " hwnd) != 0)
        return

    WinGetPos(&winX, &winY,,, "ahk_id " hwnd)
    global moveState
    moveState := {
        hwnd: hwnd,
        x: winX,
        y: winY,
        startX: startX,
        startY: startY
    }
    SetTimer(MoveWindow, 2)
}

MoveWindow() {
    global moveState

    if (!GetKeyState("LButton", "P") || !WinExist("ahk_id " moveState.hwnd)) {
        SetTimer(MoveWindow, 0)
        return
    }

    if GetKeyState("Escape", "P") {
        SetTimer(MoveWindow, 0)
        WinMove(moveState.x, moveState.y,,, "ahk_id " moveState.hwnd)
        return
    }

    CoordMode("Mouse", "Screen")
    MouseGetPos(&mouseX, &mouseY)
    x := moveState.x + mouseX - moveState.startX
    y := moveState.y + mouseY - moveState.startY
    SetWinDelay(-1)
    WinMove(x, y,,, "ahk_id " moveState.hwnd)
}
