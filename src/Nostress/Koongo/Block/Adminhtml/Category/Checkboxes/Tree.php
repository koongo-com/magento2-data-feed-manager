<?php
/**
 * Magento Module developed by NoStress Commerce
 *
 * NOTICE OF LICENSE
 *
 * This program is licensed under the Koongo software licence (by NoStress Commerce).
 * With the purchase, download of the software or the installation of the software
 * in your application you accept the licence agreement. The allowed usage is outlined in the
 * Koongo software licence which can be found under https://docs.koongo.com/display/koongo/License+Conditions
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at https://store.koongo.com/.
 *
 * See the Koongo software licence agreement for more details.
 * @copyright Copyright (c) 2017 NoStress Commerce (http://www.nostresscommerce.cz, http://www.koongo.com/)
 *
 */
namespace Nostress\Koongo\Block\Adminhtml\Category\Checkboxes;

/**
 * Categories tree with checkboxes
 *
 * @author     Nostress Team <info@nostresscommerce.c>
 */

use Magento\Framework\Data\Tree\Node;

class Tree extends \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree
{
    /**
     * Max category tree level for categories load from database
     */
    const CATEGORY_TREE_MAX_LEVEL = 10;

    /**
     * Tree representation as php array
     * @var array
     */
    protected $treeRootArray;

    /**
     * @var \Nostress\Koongo\Model\ResourceModel\Category\Tree
     */
    protected $_categoryTree;

    /**
     * Category info indexed array
     * @var array
     */
    protected $categoryIndexedArray;

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('Nostress_Koongo::catalog/category/checkboxes/tree.phtml');
    }

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Backend\Model\Auth\Session $backendSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Nostress\Koongo\Model\ResourceModel\Category\Tree $koongoCategoryTree,
        \Magento\Backend\Model\Auth\Session $backendSession,
        array $data = []
    ) {
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $jsonEncoder, $resourceHelper, $backendSession, $data);
        $this->_categoryTree = $koongoCategoryTree;
    }

    /**
     * @param mixed|null $parenNodeCategory
     * @return string
     */
    public function getTreeJson($parenNodeCategory = null)
    {
        $rootArray = $this->getTreeRootArray($parenNodeCategory);
        $json = $this->_jsonEncoder->encode(isset($rootArray['children']) ? $rootArray['children'] : []);
        return $json;
    }

    protected function getTreeRootArray($parenNodeCategory = null)
    {
        if (!isset($this->treeRootArray)) {
            $this->treeRootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory, self::CATEGORY_TREE_MAX_LEVEL));
        }
        return $this->treeRootArray;
    }

    public function getCategoriesInfoJson()
    {
        $this->categoryIndexedArray = [];
        $node = $this->getTreeRootArray();
        if (!empty($node['children'])) {
            //Skip default category if it is just one
            if (count($node['children']) == 1) {
                $node = $node['children'][0];
                //Save Default category
                $name = htmlspecialchars_decode($node['name']);
                $this->categoryIndexedArray[(int)$node['id']] = ['name' => $name,'path' => $name];
            }

            if (!empty($node['children'])) {
                foreach ($node['children'] as $childItem) {
                    $this->populateCategoriesIndexedArray($childItem, "", );
                }
            }
        }
        return json_encode($this->categoryIndexedArray);
    }

    protected function populateCategoriesIndexedArray($item, $parentPath = null)
    {
        $name = $path = htmlspecialchars_decode($item['name']);

        if (!empty($parentPath)) {
            $path = $parentPath . ' > ' . $path;
        }

        $this->categoryIndexedArray[(int)$item['id']] = ['name' => $name,'path' => $path];

        if (!empty($item['children'])) {
            foreach ($item['children'] as $childItem) {
                $this->populateCategoriesIndexedArray($childItem, $path);
            }
        }
    }

    /**
     * @param array|Node $node
     * @param int $level
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $item = parent::_getNodeJson($node, $level);
        $item['name'] = $this->escapeHtml($node->getName());
        return $item;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCategoryCollection()
    {
        $collection = parent::getCategoryCollection();
        //NoStress Koongo modification - collection must be loaded prior to usage. Otherwise categories with level >5 are not loaded correctly into tree.
        return $collection->load();
    }

    /**
     * @param mixed|null $parentNodeCategory
     * @param int $recursionLevel
     * @return Node|array|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if ($parentNodeCategory !== null && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        //NoStress Koongo modification - do not load tree from registry
        // $root = $this->_coreRegistry->registry('root');

        $root = null;
        if ($root === null) {
            $storeId = (int)$this->getRequest()->getParam('store');

            if ($storeId) {
                $store = $this->_storeManager->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            } else {
                $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            }

            //NoStress Koongo modification - category tree must be loaded up to level 10
            $this->_categoryTree->setLoaded(false);
            $tree = $this->_categoryTree->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }
            //NoStress Koongo modification - do not save tree to registry
            // $this->_coreRegistry->register('root', $root);
        }

        return $root;
    }
}
