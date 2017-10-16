<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Catalog\Product;


class View extends \Magento\Catalog\Block\Product\View
{

	/**
	 * Override this to return product which
	 * is set programmatically in block creation
	 *
	 * @return \Magento\Catalog\Model\Product
	 */
	public function getProduct()
	{
		return $this->getData('product');
	}

	/**
	 * Return identifiers for produced content
	 *
	 * @return array
	 */
	public function getIdentities()
	{
		return $this->getProduct()->getIdentities();
	}

}