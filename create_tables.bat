@echo off
"C:\xampp\mysql\bin\mysql.exe" -u root cosc360project < dbtemplate\thread_tables.sql
echo Tables created successfully.
pause 