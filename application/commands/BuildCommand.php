<?php

class BuildCommand extends CConsoleCommand 
{
    public $releaseRepo = "SamMousa/Releases";
    public $quiet = false;
    
    
    protected $_branch;
    protected $_buildNumber;
    
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
    public function actionRelease($quiet = false, $branch = null) {
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
        // Copy all except hidden files from our build dir to the new directory.
        shell_exec("cp -fR $sourceDir/* $tempDir/new");
        chdir("$tempDir/new");
        
        // Get existing tags and make sure our new tag and thus build number is unique.
        $tags = $this->git('tag');
        while(in_array($this->tag, $tags)) {
            $this->buildNumber++;
        }
        $this->updateVersion("$tempDir/new/application/config/version.php");
        $this->out("The release will be tagged '{$this->tag}'");
        $this->updateChangeLog("$tempDir/new/application/docs/ChangeLog");
        
        $this->git('add --all *');
        $this->git("commit -a -m 'Automated release {$this->tag}'");
        $this->git("tag -a {$this->tag} -m 'Automated release of {$this->tag}'");
        $this->git("push origin {$this->branch}:{$this->branch}");
        $this->git("push origin --tags");
    }
    
    public function getVersion() {
        return include __DIR__ . '/../config/version.php';
    }
    
    protected function updateVersion($file) {
        $this->out("Updating version file.");
        $config = array_merge($this->version, [
            'buildnumber' => $this->buildNumber,
            'sourceCommit' => $this->git("log -n 1 --pretty='%h'")[0],
        ]);
        $bytes = file_put_contents($file, "<?php\nreturn " . var_export($config, true) . ';');
        $this->out("Finished version file, $bytes bytes written.");
    }
    public function getVersionNumber() {
        return $this->version['versionnumber'];
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
    protected function updateChangeLog($file) {
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
        foreach (explode(chr(0), shell_exec('git log --no-merges -z --pretty="%h---%an---%B"' . $range)) as $commit) {
            $parts = explode('---', $commit);
            if (count($parts) == 3) {
                $parts[2] = array_filter(array_map('trim', explode("\n", $parts[2])));
                $changes .= $this->formatCommitMessages($parts[0], $parts[1], $parts[2]);
            }
        }
        
        if (file_exists($file)) {
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