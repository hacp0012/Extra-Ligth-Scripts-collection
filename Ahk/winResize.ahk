#Requires AutoHotkey v2.0
#NoTrayIcon
#SingleInstance Force

global resizeState := 0

~Alt & RButton::
{
    CoordMode("Mouse", "Screen")
    MouseGetPos(&startX, &startY, &hwnd)

    if (!hwnd || WinGetClass("ahk_id " hwnd) == "WorkerW")
        return
    if (WinGetMinMax("ahk_id " hwnd) != 0)
        return

    WinGetPos(&winX, &winY, &winW, &winH, "ahk_id " hwnd)
    global resizeState
    resizeState := {
        hwnd: hwnd,
        x: winX,
        y: winY,
        width: winW,
        height: winH,
        startX: startX,
        startY: startY
    }
    SetTimer(ResizeWindow, 10)
}

ResizeWindow() {
    global resizeState

    if (!GetKeyState("RButton", "P") || !WinExist("ahk_id " resizeState.hwnd)) {
        SetTimer(ResizeWindow, 0)
        return
    }

    CoordMode("Mouse", "Screen")
    MouseGetPos(&mouseX, &mouseY)
    width := Max(100, resizeState.width + mouseX - resizeState.startX)
    height := Max(60, resizeState.height + mouseY - resizeState.startY)
    WinMove(resizeState.x, resizeState.y, width, height, "ahk_id " resizeState.hwnd)
}
