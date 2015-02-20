<?php
namespace ls\cli;
use CConsoleCommand;
class BuildCommand extends CConsoleCommand 
{
    public $releaseRepo = "SamMousa/Releases";
    public $quiet = false;
    
    protected $_branch;
    protected $_buildNumber;
    protected $_versionNumber;
    
    protected function git($command, &$returnValue = null) 
    {
        $output = [];
        exec("git $command", $output, $returnValue);
        return $output;
    }
    
    protected function getBranch() {
        if (!isset($this->_branch)) {
            foreach ($this->git('branch') as $branch) {
                if (substr($branch, 0, 1) === '*') {
                    $this->_branch = trim(substr($branch, 1));
                    break;
                }
            }
        }
        return $this->_branch;
    }
    
    public function setBranch($branch) {
        $this->_branch = $branch;
    }
    
    protected function out($lines) {
        if (!$this->quiet) {
            if (!is_array($lines)) {
                $lines = [$lines];
            }
            foreach ($lines as $key => $line) {
                if (is_numeric($key)) {
                    echo "$line\n";
                } else {
                    echo "$key : $line\n";
                }
            }
            
        }
    }
    
    protected function ask($question, $default=null, $regex = null) {
        $hint = '';
        do {
            $result = readline("$question $hint [$default]: ");
            $hint = "(must match: $regex)";
        } while ($result != '' && isset($regex) && !preg_match($regex, $result));
        return $result == '' ? $default : $result;
    }
    protected function getPreviousBuildInfo() {
        // Get version file.
        $file = "https://raw.githubusercontent.com/{$this->releaseRepo}/{$this->branch}/application/config/version.php";
        $client = new \GuzzleHttp\Client();
        try {
            $result = $client->get($file);
            $version = eval(substr($result->getBody()->getContents(), 5));
        } catch (\Exception $e) {
            // Branch does not exist on repo yet.
            $version = [];
        }
        return array_merge([
            'sourceCommit' => null,
            'versionnumber' => null,
            'buildnumber' => null,
        ], $version);
        
    }
    
    public function actionRelease($interactive = 'true', $quiet = 'false', $branch = 'current', $test = 'false') {
        chdir(__DIR__ . '/../../');
        $interactive = ($interactive === 'true');
        $quiet = ($quiet === 'true');
        $test = ($test === 'true');
        $branch = ($branch != 'current') ? $branch : null;
        if ($interactive) {
            // Interactive first asks the user to specify a new version number.\
            $this->out("Interactive LimeSurvey build tool.");
            $this->versionNumber = $this->ask("Please enter a version number", $this->versionNumber, '/^\d\.\d\d$/');
            $this->updateVersionNumber();
            $this->out("Running composer install to make sure dependencies are up to date.");
            $this->out(shell_exec("composer install"));
        }
        $this->doRelease($quiet, $branch, $test);
    }
    
    protected function doRelease($quiet = false, $branch = null, $test = false) {
        $this->quiet = $quiet;
        $this->branch = $branch;
        $this->out("Starting build on branch {$this->branch}");
        $info = $this->previousBuildInfo;
        $this->out("Last build info:");
        $this->out($info);
        
        $tempDir = sys_get_temp_dir() . "/{$this->tag}-" . rand(0, 10000);
        mkdir($tempDir);
        mkdir("$tempDir/base");
        mkdir("$tempDir/new");
        
        $sourceDir = getcwd();
        // This might fail.
        $this->out($this->git("clone git@github.com:{$this->releaseRepo}.git {$tempDir}/base -b {$this->branch}", $returnValue));
        
        
        if ($returnValue == 128) {
            $this->out("Could not find matching branch on release repository. Creating it now from release repo default branch.");
            $this->out($this->git("clone git@github.com:{$this->releaseRepo}.git {$tempDir}/base", $returnValue));
            chdir("$tempDir/base");
            $this->out($this->git("checkout -b {$this->branch}"));
            $this->out($this->git("push origin {$this->branch}:{$this->branch}"));
        }
        rename("$tempDir/base/.git", "$tempDir/new/.git");
        rename("$tempDir/base/.gitignore", "$tempDir/new/.gitignore");
        
        // Copy all except hidden files from our build dir to the new directory.
        $this->out("Copying from $sourceDir to $tempDir/new");
        passthru("cp -fr $sourceDir/* $tempDir/new");   
        // Remove .git directories from composer dependencies.
        $this->out("Copying from $sourceDir to $tempDir/new");
        shell_exec("find $tempDir/new/application/vendor/ -type d -name '.git' -exec rm -rf {} \;");
        $this->out("Changing to $tempDir/new");
        chdir("$tempDir/new");
        
        
        // Get existing tags and make sure our new tag and thus build number is unique.
        $tags = $this->git('tag');
        while(in_array($this->tag, $tags)) {
            $this->buildNumber++;
        }
        $this->updateVersion("$tempDir/new/application/config/version.php");
        $this->updateChangeLog();
        
        $this->out("The release will be tagged '{$this->tag}'");
        $this->git('add --all *');
        $this->git("commit -a -m 'Automated release {$this->tag}'");
        $this->git("tag -a {$this->tag} -m 'Automated release of {$this->tag}'");
        if ($test) {
            $this->out("Not pushing changes since running in test mode.");
            $this->out("Go to $tempDir/new to view and inspect the build.");
            
        } else {
            $this->git("push origin {$this->branch}:{$this->branch}");
            $this->git("push origin --tags");
        }
    }
    
    public function getVersion() {
        return include __DIR__ . '/../config/version.php';
    }
    
    protected function updateVersion($file) {
        $this->out("Updating version file.");
        $sourceDir = __DIR__ . '/../../.git';
        $config = array_merge($this->version, [
            'versionnumber' => $this->versionNumber,
            'buildnumber' => $this->buildNumber,
            'sourceCommit' => $this->git("--git-dir {$sourceDir} log -n 1 --pretty='%h'")[0],
        ]);
        $bytes = file_put_contents($file, "<?php\nreturn " . var_export($config, true) . ';');
        $this->out("Finished version file, $bytes bytes written.");
    }
    
    protected function updateVersionNumber() {
        if ($this->version['versionnumber'] != $this->versionNumber) {
            $this->out("Updating version number.");
            $config = array_merge($this->version, [
                'versionnumber' => $this->versionNumber
            ]);
            $bytes = file_put_contents(__DIR__ . '/../config/version.php', "<?php\nreturn " . var_export($config, true) . ';');
            $this->out("Finished version file, $bytes bytes written.");
            $this->out($this->git("commit application/config/version.php -m 'Version bump to {$this->versionNumber}'"));
            $this->out($this->git("push origin"));
        } else {
            $this->out("Version number unchanged.");
        }
    }
    public function getVersionNumber() {
        if (!isset($this->_versionNumber)) {
            $this->_versionNumber = $this->version['versionnumber'];
        }
        return $this->_versionNumber;
    }
    public function setVersionNumber($version) {
        $this->_versionNumber = $version;
    }
    public function getTag() {
        return "{$this->versionNumber}.{$this->buildNumber}-{$this->stability}";
    }
    
    public function getStability() {
        switch ($this->branch) {
            case 'master':
                return 'stable';
            case 'develop':
                return 'nightly';
            default:
                return 'unstable';
        }
    }
    
    public function getBuildNumber() 
    {
        if (!isset($this->_buildNumber)) {
            $this->_buildNumber = date('ymd');
        }
        return $this->_buildNumber;
    }
    
    public function setBuildNumber($value) {
        $this->_buildNumber = $value;
    }
    
    /**
     * This function updates the change log, by prepending the changes.
     * @param type $since
     */
    protected function updateChangeLog() {
        $this->out("Updating ChangeLog");
        $since = $this->previousBuildInfo['sourceCommit'];
        if (isset($since)) {
            $range = " $since..HEAD";
        } else {
            $range = '';
        }
        $changes = "Changes from {$this->previousBuildInfo['versionnumber']}"
            . " (build {$this->previousBuildInfo['buildnumber']})"
            . " to {$this->versionNumber} (build {$this->buildNumber})\n";
            
        $sourceDir = __DIR__ . '/../../.git';
        foreach (explode(chr(0), shell_exec("git --git-dir {$sourceDir} log --no-merges -z --pretty='%h---%an---%B' $range")) as $commit) {
            $parts = explode('---', $commit);
            if (count($parts) == 3) {
                $parts[2] = array_filter(array_map('trim', explode("\n", $parts[2])));
                $changes .= $this->formatCommitMessages($parts[0], $parts[1], $parts[2]);
            }
        }
        
        $dir = getcwd();
        $file = "$dir/docs/ChangeLog";
        if (file_exists($file)) {
            $this->git("checkout -- $file");
            $changes .= file_get_contents($file);
        }
        
        $bytes = file_put_contents($file, $changes);
        $this->out("Finished ChangeLog, $bytes bytes written.");
    }
    
    protected function formatCommitMessages($hash, $author, array $messages) {
        $result = '';
        foreach($messages as $message) {
            $formatted = $this->formatCommitMessage($hash, $author, $message);
            if (isset($formatted)) {
                $result .= $formatted . "\n";
            }
        }
        return $result;
    }   
    protected function formatCommitMessage($hash, $author, $message) {
        $keywords = [
            'Fixed issue',
            'Updated feature',
            'Updated translation',
            'New feature',
            'New translation'
        ];
        // Check if message starts with a keyword:
        foreach($keywords as $keyword) {
            if (substr_compare($keyword, $message, 0, strlen($keyword), true) === 0) {
                if ($keyword == 'Updated translation') {
                    return "#" . $message;
                } else {
                    return '-' . strtr($message, ['#0' => '#']) . " ($author)";
                }
            }
        }
    }
}