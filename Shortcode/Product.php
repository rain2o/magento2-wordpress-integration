<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Shortcode;

/**
 * Class Product
 * @package FishPig\WordPress\Shortcode
 *
 * Examples:
 * [product id="1"] // Single product by ID.
 * [product ids="1,2,3"] // Listings of products by IDs.
 * [product sku="my-sku"] // Single product by SKU.
 * [product skus="sku1,sku2,sku3"] // Listing of products by SKUs.
 */
class Product extends \FishPig\WordPress\Shortcode\AbstractShortcode
{

	/**
	 * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
	 */
	protected $_productRepository;

	/**
	 * Product constructor.
	 *
	 * @param \FishPig\WordPress\Model\App $app
	 * @param \Magento\Framework\View\Element\Context $context
	 * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
	 */
	public function __construct(
		\FishPig\WordPress\Model\App $app,
		\Magento\Framework\View\Element\Context $context,
		\Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
	)
	{
		$this->_app = $app;
		$this->_factory = $app->getFactory();
		$this->_layout = $context->getLayout();
		$this->_cache = $context->getCache();
		$this->_cacheState = $context->getCacheState();
		$this->_productRepository = $productRepositoryFactory;
	}

	/**
	 * @return string
	 **/
	public function getTag()
	{
		return 'product';
	}

	/**
	 * @return $this
	 */
	protected function _process()
	{
		$value = $this->getValue();
		if (($shortcodes = $this->_getShortcodesByTag($this->getTag())) !== false) {
			foreach ($shortcodes as $it => $shortcode) {
				$params = $shortcode->getParams();
				$isListing = false;
				$products = array();
				$product = null;
				$repo = $this->_productRepository->create();

				/** single sku */
				if ($sku = $params->getSku()) {
					$product = $repo->get($sku);
				}

				/** list of skus */
				else if (($skus = trim($params->getSkus(), ',')) !== '') {
					$skus = str_replace(array('&#8217;', '&#8242;'), '', utf8_encode($skus));
					foreach (explode(',', $skus) as $sku) {
						$products[] = $repo->get($sku);
					}
					$isListing = true;
				}

				/** single id */
				else if ($productId = $params->getId()) {
					$product = $repo->getById($productId);
				}

				/** list of ids */
				else if (($ids = trim($params->getIds(), ',')) !== '') {
					foreach (explode(',', $ids) as $id) {
						$products[] = $repo->getById($id);
					}
					$isListing = true;
				}

				if ($isListing) {
					$html = $this->_layout->createBlock('\Magento\Catalog\Block\Product\ListProduct')
					                      ->setTemplate('Pyxl_WordPress::shortcode/product-listing.phtml')
					                      ->setProducts($products)
					                      ->addData($params->getData())
					                      ->setObject($this->getObject());
				} else {
					$html = $this->_layout->createBlock('\Pyxl\WordPress\Block\Catalog\Product\View')
					                      ->setProduct($product)
					                      ->setTemplate('Pyxl_WordPress::shortcode/product.phtml')
					                      ->addData($params->getData())
					                      ->setObject($this->getObject());
				}

				// replace each instance of shortcode
				$value = str_replace($shortcode['html'], $html->toHtml(), $value);

			}
			// set updated html
			$this->setValue($value);
		}

		return $this;
	}
}