!define APP_NAME "CXDash"
!define COMP_NAME "aidil.dev"
!define WEB_SITE "https://aidil.dev"
!define VERSION "0.0.1"
!define INSTALL_DIR "C:\Program Files\CXDash"
!define DEPS_PATH "C:\Users\aidilrx04\Desktop\projects\installers_deps\cxdash"
!define URL_ALIAS="cxdash.local"

Name "${APP_NAME}"
OutFile "${APP_NAME}_${VERSION}_Installer.exe"
InstallDir "${INSTALL_DIR}"
RequestExecutionLevel admin

!include "MUI2.nsh"
!define MUI_ABORTWARNING

!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_DIRECTORY
!insertmacro MUI_PAGE_INSTFILES
!insertmacro MUI_PAGE_FINISH

Var SKIP

Section "Core Application & Dependencies" SecCore
	SetOutPath "$INSTDIR"
	File /r "installer-build\*.*"

	; Optimize Laravel files
	DetailPrint "Optimizing Laravel files..."
	nsExec::ExecToLog '"$INSTDIR\dependencies\php\php.exe" "$INSTDIR\artisan" optimize'
	Pop $0
	IntCmp $0 0 optimize_success
		DetailPrint "Warning: Laravel optimization returned a non-zero exit code ($0)."
	optimize_success:

	; Add local host alias
	DetailPrint "Adding local host alias..."
	StrCpy $R0 "127.0.0.1 cxdash.local"
	nsExec::ExecToLog 'powershell -Command "Add-Content -Path ''$WINDIR\System32\drivers\etc\hosts'' -Value ''$R0''"'

	; Configure and start background services via NSSM / batch scripts
	DetailPrint "Configuring background services..."
	nsExec::ExecToLog '"$INSTDIR\configure_services.bat"'

	WriteUninstaller "$INSTDIR\uninstaller.exe"

	; Add to Add/Remove Programs
	WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${APP_NAME}" "DisplayName" "${APP_NAME}"
	WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${APP_NAME}" "UninstallString" "$INSTDIR\uninstall.exe"
	WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${APP_NAME}" "DisplayVersion" "${VERSION}"
	WriteRegStr HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${APP_NAME}" "Publisher" "${COMP_NAME}"

	IntCmp $SKIP 0 success_end
    
	skip_end:
		DetailPrint "Skipping..."
	success_end:
		DetailPrint "Done"
SectionEnd

Section "Uninstall"
    DetailPrint "Stopping and removing background services..."
    nsExec::ExecToLog '"$INSTDIR\stop_services.bat" --remove'

    ; Give Windows a brief moment to release file handles after service deletion
    Sleep 2000

    DetailPrint "Removing application files..."
    RMDir /r "$INSTDIR"

    DetailPrint "Cleaning up registry keys..."
    DeleteRegKey HKLM "Software\Microsoft\Windows\CurrentVersion\Uninstall\${APP_NAME}"
SectionEnd