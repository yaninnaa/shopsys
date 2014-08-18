<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel {

	private $configs = array();

	public function registerBundles() {
		$bundles = array(
			new Bmatzner\JQueryBundle\BmatznerJQueryBundle(),
			new Bmatzner\JQueryUIBundle\BmatznerJQueryUIBundle(),
			new Craue\FormFlowBundle\CraueFormFlowBundle(),
			new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
			new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
			new FM\ElfinderBundle\FMElfinderBundle(),
			new Fp\JsFormValidatorBundle\FpJsFormValidatorBundle(),
			new JMS\TranslationBundle\JMSTranslationBundle(),
			new Prezent\Doctrine\TranslatableBundle\PrezentDoctrineTranslatableBundle(),
			new RaulFraile\Bundle\LadybugBundle\RaulFraileLadybugBundle(),
			new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
			new SS6\AutoServicesBundle\SS6AutoServicesBundle(),
			new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
			new Symfony\Bundle\AsseticBundle\AsseticBundle(),
			new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
			new Symfony\Bundle\MonologBundle\MonologBundle(),
			new Symfony\Bundle\SecurityBundle\SecurityBundle(),
			new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
			new Symfony\Bundle\TwigBundle\TwigBundle(),
			new Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
			new Ivory\CKEditorBundle\IvoryCKEditorBundle(), // has to be loaded after FrameworkBundle and TwigBundle
			new SS6\ShopBundle\SS6ShopBundle(), // must be loaded as last, because translations must overwrite other bundles
		);

		if (in_array($this->getEnvironment(), array('dev', 'test'))) {
			$bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
			$bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
			$bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
		}

		return $bundles;
	}

	/**
	 * @param string|array $filename
	 */
	public function addConfig($filename) {
		$this->configs += (array)$filename;
	}

	public function registerContainerConfiguration(LoaderInterface $loader) {
		foreach ($this->configs as $filename) {
			if (file_exists($filename) && is_readable($filename)) {
				$loader->load($filename);
			}
		}
	}

}
