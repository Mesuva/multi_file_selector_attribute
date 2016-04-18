<?php
// Author: Ryan Hewitt - http://www.mesuva.com.au
namespace Concrete\Package\MultiFileSelectorAttribute;

use Package;
use \Concrete\Core\Attribute\Key\Category as AttributeKeyCategory;
use \Concrete\Core\Attribute\Type as AttributeType;

class Controller extends Package {

	protected $pkgHandle = 'multi_file_selector_attribute';
	protected $appVersionRequired = '5.7.5';
	protected $pkgVersion = '0.9';
	
	public function getPackageDescription() {
		return t("Attribute that allows the selection of multiple files/images");
	}
	
	public function getPackageName() {
		return t("Multi File Selector Attribute");
	}
	
	public function install() {
		parent::install();
		$pkgh = Package::getByHandle('multi_file_selector_attribute');
		$col = AttributeKeyCategory::getByHandle('collection');
		AttributeType::add('multi_file_selector', t('Multi Image/File'), $pkgh);
		$col->associateAttributeKeyType(AttributeType::getByHandle('multi_file_selector'));
	}
}