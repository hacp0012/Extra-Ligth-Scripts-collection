#Requires AutoHotkey v2.0
#SingleInstance Force

Persistent()

global childPids := []
global startupShortcut := A_Startup "\WinMove.ahk.lnk"
global helpGui := 0
global startupCheckbox := 0

OnExit(StopChildren)
A_TrayMenu.Add("Aide / Raccourcis", ShowHelp)
A_TrayMenu.Add()
A_TrayMenu.Add("Quitter", QuitApp)

for scriptName in ["winMove.ahk", "winResize.ahk", "control_keys.ahk"] {
    scriptPath := A_ScriptDir "\" scriptName
    if !FileExist(scriptPath)
        throw Error("Script introuvable : " scriptPath)

    Run('"' A_AhkPath '" "' scriptPath '"', A_ScriptDir, "Hide", &childPid)
    childPids.Push(childPid)
}

ShowHelp(*) {
    global helpGui, startupCheckbox, startupShortcut

    if !IsObject(helpGui) {
        helpGui := Gui(, "WinMove - Aide")
        helpGui.SetFont("s10", "Segoe UI")
        helpGui.Add("Text", "w450", "GESTION DES FENÊTRES")
        helpGui.Add("Text", "xm y+10 w450", "Alt + clic gauche maintenu`nDéplacer la fenêtre avec le curseur.")
        helpGui.Add("Text", "xm y+8 w450", "Alt + clic droit maintenu`nRedimensionner la fenêtre avec le curseur.")
        helpGui.Add("Text", "xm y+16 w450", "BUREAUX VIRTUELS (maintenir CapsLock)")
        helpGui.Add("Text", "xm y+8 w450", "1 à 9 : aller au bureau correspondant.")
        helpGui.Add("Text", "xm y+4 w450", "Alt + 1 à 9 : déplacer la fenêtre active vers ce bureau.")
        helpGui.Add("Text", "xm y+4 w450", "Tiret : bureau suivant `; 0 : bureau précédent.")
        helpGui.Add("Text", "xm y+4 w450",
            "Bord gauche/droit : continuer le mouvement du curseur vers le bord pour changer de bureau.")
        helpGui.Add("Text", "xm y+16 w450", "RACCOURCIS CAPSLOCK")
        helpGui.Add("Text", "xm y+8 w450",
            "H / N / J / K : haut / bas / gauche / droite.`nU : Entrée `; I : Retour arrière.`nW : onglet suivant `; S : enregistrer `; Q : fermer."
        )
        helpGui.Add("Text", "xm y+16 w450", "MENU DES BUREAUX")
        helpGui.Add("Text", "xm y+8 w450",
            "CapsLock + clic droit sur la barre de titre`nAffiche le menu pour déplacer la fenêtre vers un bureau.")
        startupCheckbox := helpGui.Add("Checkbox", "xm y+20 w450", "Lancer automatiquement au démarrage de Windows")
        startupCheckbox.OnEvent("Click", ToggleStartup)
        helpGui.OnEvent("Close", HideHelp)
        helpGui.OnEvent("Escape", HideHelp)
    }

    startupCheckbox.Value := FileExist(startupShortcut) ? 1 : 0
    helpGui.Show()
}

HideHelp(gui) {
    gui.Hide()
}

ToggleStartup(control, *) {
    global startupShortcut, startupCheckbox

    try {
        if (control.Value) {
            if !FileExist(startupShortcut)
                FileCreateShortcut(A_AhkPath, startupShortcut, A_ScriptDir, '"' A_ScriptFullPath '"', "WinMove")
        } else if FileExist(startupShortcut) {
            FileDelete(startupShortcut)
        }
    } catch as err {
        MsgBox("Impossible de modifier le lancement au démarrage :`n" err.Message, "WinMove", "Iconx")
    }

    startupCheckbox.Value := FileExist(startupShortcut) ? 1 : 0
}

QuitApp(*) {
    ExitApp()
}

StopChildren(*) {
    global childPids

    for childPid in childPids {
        if ProcessExist(childPid)
            ProcessClose(childPid)
    }
}
