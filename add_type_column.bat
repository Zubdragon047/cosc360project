@echo off
echo Adding type column to users table...
"C:\xampp\mysql\bin\mysql.exe" -u root -p cosc360project < dbtemplate/add_type_column.sql
echo Type column added successfully.
pause 