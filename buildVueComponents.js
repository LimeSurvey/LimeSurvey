/**
=============================================================================================
||        .____    .__                 _________                                           ||
||        |    |   |__| _____   ____  /   _____/__ ____________  __ ____ ___.__.           ||
||        |    |   |  |/     \_/ __ \ \_____  \|  |  \_  __ \  \/ // __ <   |  |           ||
||        |    |___|  |  Y Y  \  ___/ /        \  |  /|  | \/\   /\  ___/\___  |           ||
||        |_______ \__|__|_|  /\___  >_______  /____/ |__|    \_/  \___  > ____|           ||
||                \/        \/     \/        \/                        \/\/                ||
||    .................................................................................    ||
||          __      __                 _____                      _ _                      ||
||          \ \    / /                / ____|                    (_) |                     ||
||           \ \  / /   _  ___ ______| |     ___  _ __ ___  _ __  _| | ___ _ __            ||
||            \ \/ / | | |/ _ \______| |    / _ \| '_ ` _ \| '_ \| | |/ _ \ '__|           ||
||             \  /| |_| |  __/      | |___| (_) | | | | | | |_) | | |  __/ |              ||
||              \/  \__,_|\___|       \_____\___/|_| |_| |_| .__/|_|_|\___|_|              ||
||                                                         | |                             ||
||                                                         |_|                             ||
=============================================================================================
 * 
 * This file can be run with nodejs on the commandline
 * Example `node buildVueComponents.js´
 * 
 * It will descent into every buildable component, run yarn to get the dependencies and start the build process.
 * The full runtime will take around 1 Minute.
 * 
 * It needs as well a current nodejs (LTS preferred) as also the yarn package manager installed.
 * 
 */
const { spawn }  = require('child_process');
const { execSync }  = require('child_process');
const path = require('path');
const fs = require('fs');
const args = process.argv.slice(2)
const isWin = process.platform === "win32";

const yarnInstalled = execSync((isWin ? 'where.exe yarn' : 'which yarn'), {encoding: 'utf8'});

if(yarnInstalled.split('\n').length < 1 ) {
    console.log("=".repeat(100));
    console.error("ERROR: yarn is not installed");
    console.log(`
LimeSurvey uses the yarn package manager to build and manage dependancies.
To get it please visit 'https://yarnpkg.com/lang/en/' and follow the installation instructions.
----
If you have installed yarn, check your PATH, or restart the console.
`);
console.log("=".repeat(100));
process.exit(1);
}


const all = args.includes('-a');
const single = args.includes('-s');
const verbose = args.includes('-v');
const prepareOnly = args.includes('-p');

if(!all && !single) {
    console.log(`
=============================================================================================
||        .____    .__                 _________                                           ||
||        |    |   |__| _____   ____  /   _____/__ ____________  __ ____ ___.__.           ||
||        |    |   |  |/     \\_/ __ \\ \\_____  \\|  |  \\_  __ \\  \\/ // __ <   |  |           ||
||        |    |___|  |  Y Y  \\  ___/ /        \\  |  /|  | \\/\\   /\\  ___/\\___  |           ||
||        |_______ \\__|__|_|  /\\___  >_______  /____/ |__|    \\_/  \\___  > ____|           ||
||                \\/        \\/     \\/        \\/                        \\/\\/                ||
||    .................................................................................    ||
||          __      __                 _____                      _ _                      ||
||          \\ \\    / /                / ____|                    (_) |                     ||
||           \\ \\  / /   _  ___ ______| |     ___  _ __ ___  _ __  _| | ___ _ __            ||
||            \\ \\/ / | | |/ _ \\______| |    / _ \\| '_ \` _ \\| '_ \\| | |/ _ \\ '__|           ||
||             \\  /| |_| |  __/      | |___| (_) | | | | | | |_) | | |  __/ |              ||
||              \\/  \\__,_|\\___|       \\_____\\___/|_| |_| |_| .__/|_|_|\\___|_|              ||
||                                                         | |                             ||
||                                                         |_|                             ||
=============================================================================================
||   Usage :                                                                               ||
||     Either use '-a' to build all components                                             ||
||     Or use '-s [componentname]' to build one specific component                         ||
||     Use '-s' without component name to get a list of possible components                ||
||                                                                                         ||
||   Options:                                                                              ||
||     -v -> Verbose mode, show all build processes                                        ||
||     -p -> Only prepare (install dependancies)                                           ||
||                                                                                         ||
=============================================================================================
`);
process.exit(0);
}

const runGetDependenciesInFolder = function (folder) {
    return new Promise((resolve, reject) => {
        console.log(`|| ===  Descending into ${folder} and running 'yarn'`);
        const fullPath = folder; //path.normalize(folder);
        const command = spawn('yarn', ['-c'], {cwd:fullPath, shell:true, stdio: [ 'pipe', (verbose ? process.stdout : 'ignore'), process.stderr ]});

        command.on('error', (err) => {
            console.log(err);
            reject();
        })

        command.on('close', (code) => {
            console.log("|| ===  Successfully prepared.\n"+"".repeat(32));
            resolve(folder);
        });
    });
};

const runBuildFolder = function (folder) {
    return new Promise((resolve, reject) => {
        console.log(`|| === Descending into ${folder} and running 'yarn build'`);
        const fullPath = folder; //path.normalize(folder);
        const command = spawn('yarn build', ['-c'], {cwd:fullPath, shell:true, stdio: [ 'pipe', (verbose ? process.stdout : 'ignore'), process.stderr ]});

        command.on('error', (err) => {
            console.log(err);
            reject();
        })

        command.on('close', (code) => {
            console.log("|| === Successfully built.\n"+"".repeat(32));
            resolve();
        });
    });
};

const runBuild = function() {
    const startTime = new Date();
    const pathArray = [
        ['adminbasics', 'assets/packages/adminbasics/'],
        ['adminsidepanel', 'assets/packages/adminsidepanel/'],
        ['admintoppanel', 'assets/packages/admintoppanel/'],
        ['datasecuritysettings', 'assets/packages/datasecuritysettings/'],
        ['emailtemplates', 'assets/packages/emailtemplates/'],
        ['filemanager', 'assets/packages/filemanager/'],
        ['labelsets', 'assets/packages/labelsets/'],
        ['panelintegration', 'assets/packages/panelintegration/'],
        ['questioneditor', 'assets/packages/questioneditor/'],
        ['questiongroup', 'assets/packages/questiongroup/'],
        ['textelements', 'assets/packages/textelements/'],
    ];

    console.log(`
=============================================================================================
||        .____    .__                 _________                                           ||
||        |    |   |__| _____   ____  /   _____/__ ____________  __ ____ ___.__.           ||
||        |    |   |  |/     \\_/ __ \\ \\_____  \\|  |  \\_  __ \\  \\/ // __ <   |  |           ||
||        |    |___|  |  Y Y  \\  ___/ /        \\  |  /|  | \\/\\   /\\  ___/\\___  |           ||
||        |_______ \\__|__|_|  /\\___  >_______  /____/ |__|    \\_/  \\___  > ____|           ||
||                \\/        \\/     \\/        \\/                        \\/\\/                ||
||    .................................................................................    ||
||          __      __                 _____                      _ _                      ||
||          \\ \\    / /                / ____|                    (_) |                     ||
||           \\ \\  / /   _  ___ ______| |     ___  _ __ ___  _ __  _| | ___ _ __            ||
||            \\ \\/ / | | |/ _ \\______| |    / _ \\| '_ \` _ \\| '_ \\| | |/ _ \\ '__|           ||
||             \\  /| |_| |  __/      | |___| (_) | | | | | | |_) | | |  __/ |              ||
||              \\/  \\__,_|\\___|       \\_____\\___/|_| |_| |_| .__/|_|_|\\___|_|              ||
||                                                         | |                             ||
||                                                         |_|                             ||
=============================================================================================
`);

if(!single) {
    console.log(`
|| ===  Starting to ${(prepareOnly ? 'prepare' : 'compile')} the components ${(verbose ? 'and using verbose mode.' : '.')}`);
    const finalPromise = pathArray.reduce( 
        async (promise, item) => {
            try{
                await promise;
            } catch(e) { throw e; }
            console.log(`=====| ${(prepareOnly ? 'Preparing' : 'Building' )} ${item[0]} |====`);
            if(prepareOnly) {
                return runGetDependenciesInFolder(item[1]);
            } else {
                return runGetDependenciesInFolder(item[1]).then(runBuildFolder);
            }
        },
        Promise.all([
            runGetDependenciesInFolder('assets/packages/meta/LsCkeditor'),
            runGetDependenciesInFolder('assets/packages/meta/LsRTLPlugin')
        ])
    );
    
    finalPromise.then(()=> {
        const endTime = new Date();
        const difference = endTime - startTime;
        const minutes = String((difference*1000*60)%60).padStart(2,'0');
        const seconds = String((difference*1000)%60).padStart(2,'0');
        const milliseconds = String(difference%1000).padStart(4,'0');
        if(verbose) {
            console.log(`|| === Started at ${startTime.toLocaleTimeString('de-DE')}`);
            console.log(`|| === Ended at ${endTime.toLocaleTimeString('de-DE')}`);
            console.log(`|| === Total milliseconds ${difference}`);
        }
        console.log(`|| === All build in ${minutes}:${seconds}.${milliseconds}`);
    });   

} else {
    let getSPosition = args.indexOf('-s');
    const componentToBuild = args[getSPosition+1];
    componentToBuildArray = pathArray.filter((item) => { return item[0] == componentToBuild });

    if(componentToBuildArray.length > 1 || componentToBuildArray.array == 0 || componentToBuild == undefined) {
        console.error("|| ===  Component not found or ambiguous, possible options are:")
        pathArray.forEach((item) => {
            console.log(`||       => ${item[0]}, (${item[1]})`);
        });
        process.exit(1);
    }

    console.log(`
|| ===  Starting to ${(prepareOnly ? 'prepare' : 'compile')} the component ${componentToBuild}${(verbose ? ' and using verbose mode.' : '.')}`);

    const finalPromise = componentToBuildArray.reduce( 
        async (promise, item) => {
            try{
                await promise;
            } catch(e) { throw e; }
            console.log(`=====| ${(prepareOnly ? 'Preparing' : 'Building' )} ${item[0]} |====`);
            if(prepareOnly) {
                return runGetDependenciesInFolder(item[1]);
            } else {
                return runGetDependenciesInFolder(item[1]).then(runBuildFolder);
            }
        },
        Promise.all([
            runGetDependenciesInFolder('assets/packages/meta/LsCkeditor'),
            runGetDependenciesInFolder('assets/packages/meta/LsRTLPlugin')
        ])
    );

    finalPromise.then(()=> {
        const endTime = new Date();
        const difference = endTime - startTime;
        const minutes = String((difference*1000*60)%60).padStart(2,'0');
        const seconds = String((difference*1000)%60).padStart(2,'0');
        const milliseconds = String(difference%1000).padStart(4,'0');
        if(verbose) {
            console.log(`|| === Started at ${startTime.toLocaleTimeString('de-DE')}`);
            console.log(`|| === Ended at ${endTime.toLocaleTimeString('de-DE')}`);
            console.log(`|| === Total milliseconds ${difference}`);
        }
        console.log(`|| === All build in ${minutes}:${seconds}.${milliseconds}`);
    }); 
}

};

runBuild();
