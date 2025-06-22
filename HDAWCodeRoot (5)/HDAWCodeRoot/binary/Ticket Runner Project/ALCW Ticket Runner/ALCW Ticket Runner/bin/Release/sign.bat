cd "C:\EIB\ALCWCodeRoot\binary\Ticket Runner Project\ALCW Ticket Runner\ALCW Ticket Runner\bin\Release"
SignTool sign /a /f "EIB.pfx" /p alacarte! /t http://timestamp.verisign.com/scripts/timestamp.dll ALCWTicketRunner.exe
pause
echo "done"
