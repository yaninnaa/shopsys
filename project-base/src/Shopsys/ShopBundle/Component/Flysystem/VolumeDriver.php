<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Component\Flysystem;

use Barryvdh\elFinderFlysystemDriver\Driver;
use elFinder;
use Shopsys\ShopBundle\Component\Flysystem\Plugin\GetUrl;

class VolumeDriver extends Driver
{
    protected function configure()
    {
        @parent::configure();

        // set thumbnails path
        $path = $this->options['tmbPath'];

        if ($path) {
            if (!$this->fs->has($path)) {
                if ($this->_mkdir($path, '')) {
                    $this->_chmod($path, $this->options['tmbPathMode']);
                } else {
                    $path = '';
                }
            }

            $stat = $this->_stat($path);

            if ($this->_dirExists($path) && $stat['read']) {
                $this->tmbPath = $path;
                $this->tmbPathWritable = $stat['write'];
            }
        }

        $this->fs->addPlugin(new GetUrl($this->options));
    }

    /**
     * @param string $hash
     */
    public function tmb($hash)
    {
        $path = $this->decode($hash);
        $stat = $this->_stat($path, $hash);

        if (isset($stat['tmb'])) {
            $res = $stat['tmb'] == '1' ? $this->createTmb($path, $stat) : $stat['tmb'];

            if (!$res) {
                list($type) = explode('/', $stat['mime']);
                $fallback = $this->options['resourcePath'] . DIRECTORY_SEPARATOR . strtolower($type) . '.png';
                if (is_file($fallback)) {
                    $res = $this->tmbname($stat);
                    if (!$this->fs->put($fallback, $this->tmbPath . DIRECTORY_SEPARATOR . $res)) {
                        $res = false;
                    }
                }
            }
            return $res;
        }
        return false;
    }

    /**
     * @param string $path
     * @param array $stat
     */
    protected function gettmb($path, $stat)
    {
        if ($this->tmbURL && $this->tmbPath) {
            // file itself thumnbnail
            if (strpos($path, $this->tmbPath) === 0) {
                return basename($path);
            }

            $stat['hash'] = $stat['hash'] ?? '';
            $name = $this->tmbname($stat);
            if ($this->fs->has($this->tmbPath . DIRECTORY_SEPARATOR . $name)) {
                return $name;
            }
        }
        return false;
    }

    /**
     * @param string $path
     * @param array $stat
     */
    protected function createTmb($path, $stat)
    {
        $tmpTmbPath = $this->tmbPath;
        $this->tmbPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tmpTmbPath;
        @mkdir($this->tmbPath, 0777, true);

        $name = parent::createTmb($path, $stat);
        if ($name !== false) {
            $fp = fopen($this->tmbPath . DIRECTORY_SEPARATOR . $name, 'rb');
            if ($fp === false) {
                return false;
            }
            $this->_save($fp, $tmpTmbPath, $name, $stat);
            unlink($this->tmbPath . DIRECTORY_SEPARATOR . $name);
        }
    }

    /**
     * @param array $stat
     */
    protected function rmTmb($stat)
    {
        $path = $stat['realpath'];
        if ($this->tmbURL) {
            $tmb = $this->gettmb($path, $stat);
            $stat['tmb'] = $tmb ? $tmb : 1;
        }

        if ($this->tmbPathWritable) {
            if ($stat['mime'] === 'directory') {
                foreach ($this->scandirCE($this->decode($stat['hash'])) as $p) {
                    elFinder::extendTimeLimit(30);
                    $name = $this->basenameCE($p);
                    $name != '.' && $name != '..' && $this->rmTmb($this->stat($p));
                }
            } elseif (!empty($stat['tmb']) && $stat['tmb'] != '1') {
                $tmb = $this->tmbPath . DIRECTORY_SEPARATOR . rawurldecode($stat['tmb']);
                $this->_unlink($tmb);
                clearstatcache();
            }
        }
    }

    /**
     * @param string $path
     * @param string $hash
     */
    protected function _stat($path, $hash = '')
    {
        $stat = parent::_stat($path);
        if ($hash !== '') {
            $stat['hash'] = $hash;
        }

        if ($this->tmbURL && !isset($stat['tmb']) && $this->canCreateTmb($path, $stat)) {
            $tmb = $this->gettmb($path, $stat);
            $stat['tmb'] = $tmb ? $tmb : 1;
        }
        return $stat;
    }
}
