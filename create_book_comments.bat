@echo off
"C:\xampp\mysql\bin\mysql.exe" -u root cosc360project < dbtemplate\book_comments.sql
echo Book comments table created successfully.
pause 