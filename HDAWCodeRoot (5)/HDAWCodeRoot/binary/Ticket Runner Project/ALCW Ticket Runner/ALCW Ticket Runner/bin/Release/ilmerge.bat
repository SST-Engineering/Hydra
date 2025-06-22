cd "C:\EIB\ALCWCodeRoot\binary\Ticket Runner Project\ALCW Ticket Runner\ALCW Ticket Runner\bin\Release"
"C:\EIB\ALCWCodeRoot\binary\Ticket Runner Project\ILMerge.exe" /out:ALCWTicketRunner.exe /t:winexe /targetplatform:v4 /log:ilmerge.log "C:\EIB\ALCWCodeRoot\binary\Ticket Runner Project\ALCW Ticket Runner\ALCW Ticket Runner\bin\Release\ALCW Ticket Runner.exe" "C:\EIB\ALCWCodeRoot\binary\Ticket Runner Project\ALCW Ticket Runner\ALCW Ticket Runner\bin\Release\Ionic.Zip.dll"
pause
SignTool sign /a /f "EIB.pfx" /p alacarte! /t http://timestamp.verisign.com/scripts/timestamp.dll ALCWTicketRunner.exe
pause
del ALCWTicketRunner.zip
del ALCWTicketRunner.alcb
zip ALCWTicketRunner.zip ALCWTicketRunner.exe
pause
rename ALCWTicketRunner.zip ALCWTicketRunner.alcb
echo "done"
pause