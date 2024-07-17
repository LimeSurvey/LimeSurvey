const fs = require("fs");
const path = require("path");

const tempFolderPath = "./tmp";

function deleteFolderRecursive(folderPath, exclusions = []) {
    if (fs.existsSync(folderPath)) {
        fs.readdirSync(folderPath).forEach((file, index) => {
            const curPath = path.join(folderPath, file);
            if (!exclusions.includes(curPath)) {
                if (fs.lstatSync(curPath).isDirectory()) {
                    deleteFolderRecursive(curPath, exclusions);
                } else {
                    fs.unlinkSync(curPath);
                }
            } else {
                console.log(`Skipped file: ${curPath}`);
            }
        });
        const isDirectoryEmpty = fs.readdirSync(folderPath).length === 0;
        if (isDirectoryEmpty && !exclusions.includes(folderPath)) {
            fs.rmdirSync(folderPath);
            console.log(`Deleted folder: ${folderPath}`);
        } else {
            console.log(`Skipped folder: ${folderPath}`);
        }
    } else {
        console.log(`Folder does not exist: ${folderPath}`);
    }
}

deleteFolderRecursive(tempFolderPath, [
    "./tmp",
    "tmp/index.html",
    "tmp/upload/index.html",
    "tmp/runtime/index.html",
    "tmp/assets/index.html",
]);
