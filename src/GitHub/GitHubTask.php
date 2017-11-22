<?php
/**
 * MIT License
 *
 * Copyright (c) 2017 Dieter Raber
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace draber\CldrGen\GitHub;

/**
 * Class GitHubTask
 *
 * @package draber\CldrGen\GitHub
 */
class GitHubTask
{

    private $dataDir;

    /**
     * GitHubTask constructor.
     */
    public function __construct() {
        $this->dataDir = dirname(dirname(__DIR__)) . '/data';
    }

    /**
     * Update all repos
     */
    public function updateCldr() {
        foreach($this->getAvailableRepos() as $repo) {
            $this->downloadRepo($repo['name']);
        }
    }

    /**
     * Pull a list of all available repos using the GitHub API v3
     *
     * @return mixed
     * @throws \Exception
     */
    public function getAvailableRepos()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/orgs/unicode-cldr/repos');
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: draber/CldrGen']);

        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno  = curl_errno($ch);
        $error  = curl_error($ch);

        if($errno) {
            throw new \Exception('cURL error ' . $errno . ': ' . $error);
        }
        if($status !== 200) {
            throw new \Exception('HTTP error ' . $status);
        }

        curl_close($ch);

        return json_decode($result, true);
    }


    /**
     * Clone or update a repository
     *
     * @param $repo
     */
    protected function downloadRepo($repo) {
        $repoDir = $this->dataDir . '/' . $repo;
        if(!is_dir($repoDir)){
            mkdir($repoDir, 0755, true);
        }
        chdir($repoDir);
        exec('git init');
        exec('git pull https://github.com/unicode-cldr/' . $repo . '.git');
    }


}