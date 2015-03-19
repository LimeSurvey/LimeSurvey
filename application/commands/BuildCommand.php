<?php
namespace ls\cli;
use CConsoleCommand;
use Icecave\SemVer\Version;
class BuildCommand extends CConsoleCommand 
{
    public $releaseRepo = "SamMousa/Releases";
    public $quiet;
    public $interactive;
    
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
    protected function askBoolean($question, $default = true) {
        return $this->ask($question, $default ? 'y' : 'n', '/y|n/') === 'y';
    }
    /**
     * Asks questions to decide the next version number.
     * @return version;
     */
    protected function askVersionQuestions() 
    {
        $version = $this->getVersion();
        $stabilities = [
            'alpha',
            'beta',
            'rc',
            'stable'
        ];
        $this->out("Current version: " . $version);
        if (!$version->isStable()) {
            $this->out("You are currently on an unstable version.");
            if ($this->askBoolean("Does this change the stability?")) {
                $next = $stabilities[array_search($version->preReleaseVersionParts()[0], $stabilities) + 1];
                $stability = $this->ask("What is the new stability? Pick from: " . implode(', ', $stabilities), $next, "/" . implode('|', $stabilities) . "/");
                if ($stability == 'stable') {
                    $version->setPreReleaseVersion(null);
                } else {
                    $version->setPreReleaseVersion("$stability.1");
                }
            } else {
                $parts = $version->preReleaseVersionParts();
                $parts[1]++;
                $version->setPreReleaseVersion(implode('.', $parts));
            }
        } else {
            $major = $this->ask("Does this version change public API changes that are backwards-INCOMPATIBLE?", 'y', '/y|n/') === 'y';
            if (!$major) {
                $minor = $this->ask("Does this version contain new features?", 'y', '/y|n/') === 'y';
                if (!$minor) {
                    $version->setPatch($version->patch() + 1);
                } else {
                    $version->setMinor($version->minor() + 1);
                }
            } else {
                $version->setMajor($version->major() + 1);
            }
            
        }
        return $version->string() . '+' . $this->getBuildNumber();
    }
    
    public function actionRelease($interactive = 'true', $quiet = 'false', $branch = 'current', $test = 'false') {
        chdir(__DIR__ . '/../../');
        $this->interactive = ($interactive === 'true');
        $this->quiet = ($quiet === 'true');
        $test = ($test === 'true');
        $branch = ($branch != 'current') ? $branch : null;
        if ($this->interactive) {
            // Interactive first asks the user to specify a new version number.\
            $this->out("Interactive LimeSurvey build tool.");
            $version = $this->askVersionQuestions();
            $this->out("Running composer install to make sure dependencies are up to date.");
            $this->out(shell_exec("composer install"));
        } else {
            $version = $this->getVersion()->getVersion() . '+' . $this->getBuildNumber();
        }
        $this->doRelease($version, $quiet, $branch, $test);
    }
    
    protected function doRelease($version, $quiet = false, $branch = null, $test = false) {
        $this->quiet = $quiet;
        $this->branch = $branch;
        $this->out("Starting build on branch {$this->branch}, version: $version");
        $tempDir = sys_get_temp_dir() . "/{$version}-" . rand(0, 10000);
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
        $this->updateVersion($version, "$tempDir/new/application/config/version.php");
        $this->updateChangeLog($version);
        
        $this->out("The release will be tagged '{$version}'");
        $this->git('add --all *');
        $this->git("commit -a -m 'Automated release {$version}'");
        $this->git("tag -a {$version} -m 'Automated release of {$version}'");
        if ($test) {
            $this->out("Not pushing changes since running in test mode.");
            $this->out("Go to $tempDir/new to view and inspect the build.");
            
        } else {
            $this->git("push origin {$this->branch}:{$this->branch}");
            $this->git("push origin --tags");
        }
    }
    
    /**
     * 
     * @return Version
     */
    public function getVersion() {
        return Version::parse(App()->params['version']);
    }
    
    protected function updateVersion($version, $file) {
        $this->out("Updating version file.");
        $bytes = file_put_contents($file, "<?php\nreturn " . var_export($version, true) . ';');
        $this->out("Finished version file, $bytes bytes written.");
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
    protected function updateChangeLog($version) {
        $dir = getcwd();
        $file = "$dir/docs/ChangeLog";
        if (file_exists($file)) {
            $this->git("checkout -- $file");
        }
        // Existing changes.
        $changeLog = fopen($file, 'r');
        $line = fread($changeLog, 46);
        if (strlen($line) != 46) {
            $range = '';
        } else {
            $lastCommit = substr($line, 3, 40);
            $range =  $lastCommit. '...HEAD';
        }
        $sourceDir = __DIR__ . '/../../.git';
        $current = $this->git("--git-dir {$sourceDir} rev-parse  --verify HEAD")[0];
        if (isset($lastCommit) && $lastCommit == $current) {
            if (!$this->interactive || !$this->askBoolean('Build contains no new commits. Continue?')) {
                throw new \Exception("Build aborted. No new commits in build.");
            }
        }
        $this->out("Generating change log.");
        $changes = "(((" . $current . ")))\n";
        $changes .= "--------------------------------------------------\n";
        $changes .= "Version: $version\n";
        $changes .= "--------------------------------------------------\n";
        foreach (explode(chr(0), shell_exec("git --git-dir {$sourceDir} log --no-merges -z --pretty='%h---%an---%B' $range")) as $commit) {
            $parts = explode('---', $commit);
            if (count($parts) == 3) {
                $parts[2] = array_filter(array_map('trim', explode("\n", $parts[2])));
                $changes .= $this->formatCommitMessages($parts[0], $parts[1], $parts[2]);
            }
        }
        $changes .= file_get_contents($file);
        
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