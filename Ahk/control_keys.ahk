#Requires AutoHotkey v2.0
#SingleInstance force

; #HotIf WinActive("ahk_exe Code.exe")

;UP
~CapsLock & j::
{
  Send("{Up}")
}

;DOWN
~CapsLock & m::
{
  Send("{Down}")
}

;LEFT
~CapsLock & k::
{
  Send("{Left}")
}

;RIGHT
~CapsLock & l::
{
  Send("{Right}")
}

;ENTER
~CapsLock & u::
{
  Send("{Enter}")
}

;BACKSPACE
~CapsLock & i::
{
  Send("{BackSpace}")
}

; #HotIf