<?php

class Gcc_Mage_Page_Block_Html_Head
    extends Mage_Page_Block_Html_Head
{
    /**
     * Merge static and skin files of the same format into 1 set of HEAD directives or even into 1 directive
     *
     * Will attempt to merge into 1 directive, if merging callback is provided. In this case it will generate
     * filenames, rather than render urls.
     * The merger callback is responsible for checking whether files exist, merging them and giving result URL
     * -- Mod: Add file modification time to URL for caching purposes
     *
     * @param string $format - HTML element format for sprintf('<element src="%s"%s />', $src, $params)
     * @param array $staticItems - array of relative names of static items to be grabbed from js/ folder
     * @param array $skinItems - array of relative names of skin items to be found in skins according to design config
     * @param callback $mergeCallback
     * @return string
     */
    protected function &_prepareStaticAndSkinElements($format, array $staticItems, array $skinItems, $mergeCallback = null)
    {
        $designPackage = Mage::getDesign();
        $baseJsUrl = Mage::getBaseUrl('js');
        $baseJsDir = Mage::getBaseDir() . DS . 'js' . DS;
        $items = array();
        if ($mergeCallback && !is_callable($mergeCallback)) {
            $mergeCallback = null;
        }

        // get static files from the js folder, no need in lookups
        foreach ($staticItems as $params => $rows) {
            foreach ($rows as $name) {
                if ($mergeCallback) {
                    $items[$params][] = $baseJsDir . $name;
                } else {
                    if (file_exists($baseJsDir . $name)) {
                        $path = pathinfo($name);
                        if (array_key_exists('extension', $path)
                         && in_array($path['extension'], array(
                                'css',
                                'js',
                                'png',
                                'jpg',
                                'gif',
                            ))
                        ) {
                            $mtime = filemtime($baseJsDir . $name);
                            $name = ($path['dirname'] != '.' ? $path['dirname'] . DS : '')
                                  . $path['filename'] . '.' . $mtime . '.'
                                  . $path['extension'];
                        }
                    }
                    $items[$params][] = $baseJsUrl . $name;
                }
            }
        }

        // lookup each file basing on current theme configuration
        foreach ($skinItems as $params => $rows) {
            foreach ($rows as $name) {
                if ($mergeCallback) {
                    $items[$params][] = $designPackage->getFilename($name, array('_type' => 'skin'));
                } else {
                    $items[$params][] = $designPackage->getSkinUrl($name, array());
                }
            }
        }

        $html = '';
        foreach ($items as $params => $rows) {
            // attempt to merge
            $mergedUrl = false;
            if ($mergeCallback) {
                $mergedUrl = call_user_func($mergeCallback, $rows);
            }
            // render elements
            $params = trim($params);
            $params = $params ? ' ' . $params : '';
            if ($mergedUrl) {
                $html .= sprintf($format, $mergedUrl, $params);
            } else {
                foreach ($rows as $src) {
                    $html .= sprintf($format, $src, $params);
                }
            }
        }
        return $html;
    }
}
