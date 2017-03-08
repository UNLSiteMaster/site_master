<?php
namespace SiteMaster\Core;

class ThemeHelper {

    /**
     * Trim the base url off of a given url
     * 
     * @param string $base_url
     * @param string $url
     * @return mixed
     */
    public function trimBaseURL($base_url, $url)
    {
        //Make both agnostic to handle differences between http and https
        $base_url = $this->makeAgnostic($base_url);
        $url = $this->makeAgnostic($url);
        
        //Now trim off the base url
        return '/' . str_ireplace($base_url, '', $url);
    }

    /**
     * Strip the http or https from a URL to make it agnostic
     *
     * @param $absolute_uri
     * @return mixed
     */
    protected function makeAgnostic($absolute_uri)
    {
        return preg_replace('/^https?:\/\//', '//', $absolute_uri);
    }

    /**
     * Helper function to format a grade.  If it was graded via site_pass_fail, it will return the percent grade and letter grade,
     * otherwise, just the letter grade.
     * 
     * @param $percent_grade
     * @param $letter_grade
     * @param $site_pass_fail
     * @return string
     */
    public function formatGrade($percent_grade, $letter_grade, $site_pass_fail)
    {
        if ($site_pass_fail) {
            return $percent_grade . "% (" . $letter_grade . ")";
        } else {
            return $letter_grade;
        }
    }
}
