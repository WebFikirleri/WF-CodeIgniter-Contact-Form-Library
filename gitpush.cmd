@echo off
echo Set commit message:
SET /P cmsg=
git add *
git commit -m "%cmsg%"
git push -u origin --all
pause