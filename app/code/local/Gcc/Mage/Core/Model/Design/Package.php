<?php

class Gcc_Mage_Core_Model_Design_Package
    extends Mage_Core_Model_Design_Package
{
    /**
     * Get skin file url
     * -- Mod: Add file modification time to URL for caching purposes
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getSkinUrl($file = null, array $params = array())
    {
        Varien_Profiler::start(__METHOD__);
        if (empty($params['_type'])) {
            $params['_type'] = 'skin';
        }
        if (empty($params['_default'])) {
            $params['_default'] = false;
        }
        $this->updateParamDefaults($params);
        if (!empty($file)) {
            $result = $this->_fallback($file, $params, array(
                array(),
                array('_theme' => $this->getFallbackTheme()),
                array('_theme' => self::DEFAULT_THEME),
            ));
        }
        if (!empty($file)) {
            $filename = $this->getFilename($file, array('_type' => 'skin'));
            if (file_exists($filename)) {
                $path = pathinfo($file);
                if (array_key_exists('extension', $path)
                 && in_array($path['extension'], array(
                        'css',
                        'js',
                        'png',
                        'jpg',
                        'gif',
                    ))
                ) {
                    $mtime = filemtime($filename);
                    $file = ($path['dirname'] != '.' ? $path['dirname'] . DS : '')
                          . $path['filename'] . '.' . $mtime . '.'
                          . $path['extension'];
                }
            }
        }
        $result = $this->getSkinBaseUrl($params) . (empty($file) ? '' : $file);
        Varien_Profiler::stop(__METHOD__);
        return $result;
    }

    /**
     * Merge specified javascript files and return URL to the merged file on success
     * -- Mod: Add file modification time to URL for caching purposes
     *
     * @param $files
     * @return string
     */
    public function getMergedJsUrl($files)
    {
        $targetFilename = md5(implode(',', $files)) . '.js';
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        $targetPath = $targetDir . DS . $targetFilename;
        if ($this->_mergeFiles($files, $targetPath, false, null, 'js')) {
            if (file_exists($targetPath)) {
                $path = pathinfo($targetFilename);
                if (array_key_exists('extension', $path)
                 && in_array($path['extension'], array(
                        'css',
                        'js',
                        'png',
                        'jpg',
                        'gif',
                    ))
                ) {
                    $mtime = filemtime($targetPath);
                    $targetFilename = ($path['dirname'] != '.' ? $path['dirname'] . DS : '')
                                    . $path['filename'] . '.' . $mtime . '.'
                                    . $path['extension'];
                }
            }
            return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename;
        }
        return '';
    }

    /**
     * Merge specified css files and return URL to the merged file on success
     * -- Mod: Add file modification time to URL for caching purposes
     *
     * @param $files
     * @return string
     */
    public function getMergedCssUrl($files)
    {
        // secure or unsecure
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $targetDir = $this->_initMergerDir($mergerDir);
        if (!$targetDir) {
            return '';
        }

        // base hostname & port
        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }

        // merge into target file
        $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}") . '.css';
        $targetPath = $targetDir . DS . $targetFilename;
        if ($this->_mergeFiles($files, $targetPath, false, array($this, 'beforeMergeCss'), 'css')) {
            if (file_exists($targetPath)) {
                $path = pathinfo($targetFilename);
                if (array_key_exists('extension', $path)
                 && in_array($path['extension'], array(
                        'css',
                        'js',
                        'png',
                        'jpg',
                        'gif',
                    ))
                ) {
                    $mtime = filemtime($targetPath);
                    $targetFilename = ($path['dirname'] != '.' ? $path['dirname'] . DS : '')
                                    . $path['filename'] . '.' . $mtime . '.'
                                    . $path['extension'];
                }
            }
            return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
        }
        return '';
    }
}
