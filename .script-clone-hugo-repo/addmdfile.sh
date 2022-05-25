#echo "file sourcepath: $1"
#echo "file destinationpath: $2"
#echo "filenames: $3"
#echo "script_path: $4"
#echo "Repo to clone: $5"

#!/bin/bash
sourcepath=$1
destinationpath=$2
filename=$3
script_path=$4
script_path=$4
repo_url=$5

echo "file upload started ..."



# Remove Git Folder
rm -rf releases.aspose.com
rm -rf newfile

mkdir newfile

cd newfile

cp $sourcepath $filename

cd ..


# Clone Repo
git clone $repo_url

git config user.name "Fahad Adeel"
git config user.email "fahadadeel@gmail.com"

cd releases.aspose.com/$destinationpath

cp $script_path/newfile/$filename $filename

git add .


#git commit -m 'new Release added '
#git push origin main

# Remove Git Folder
rm -rf releases.aspose.com
rm -rf newfile

echo "file upload finished!"