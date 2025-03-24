@echo off
"C:\xampp\mysql\bin\mysql.exe" -u root cosc360project < dbtemplate\book_tables.sql
echo Book tables created successfully.
pause 