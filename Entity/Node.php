<?php

namespace Kunstmaan\AdminNodeBundle\Entity;

use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\AdminBundle\Helper\ClassLookup;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Kunstmaan\AdminNodeBundle\Form\NodeAdminType;

/**
 * Node
 *
 * @ORM\Entity(repositoryClass="Kunstmaan\AdminNodeBundle\Repository\NodeRepository")
 * @ORM\Table(name="kuma_nodes")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Node extends AbstractEntity
{

    /**
     * @ORM\ManyToOne(targetEntity="Node", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\Column(type="integer", nullable=false, name="sequence_number")
     */
    protected $sequenceNumber;

    /**
     * @ORM\OneToMany(targetEntity="Node", mappedBy="parent")
     * @ORM\OrderBy({"sequenceNumber" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="NodeTranslation", mappedBy="node")
     */
    protected $nodeTranslations;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $deleted;

    /**
     * @ORM\Column(type="boolean", name="hidden_from_nav")
     */
    protected $hiddenFromNav;

    /**
     * @ORM\Column(type="string", nullable=false, name="ref_entity_name")
     */
    protected $refEntityName;

    /**
     * @ORM\Column(type="string", nullable=true, name="internal_name")
     */
    protected $internalName;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->children         = new ArrayCollection();
        $this->nodeTranslations = new ArrayCollection();
        $this->deleted          = false;
        $this->hiddenFromNav    = false;
    }

    /**
     * @return bool
     */
    public function isHiddenFromNav()
    {
        return $this->hiddenFromNav;
    }

    /**
     * @return bool
     */
    public function getHiddenFromNav()
    {
        return $this->hiddenFromNav;
    }

    /**
     * @param bool $hiddenFromNav
     *
     * @return Node
     */
    public function setHiddenFromNav($hiddenFromNav)
    {
        $this->hiddenFromNav = $hiddenFromNav;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children->filter(
            function ($entry) {
                if ($entry->isDeleted()) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * @param ArrayCollection $children
     *
     * @return Node
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Add children
     *
     * @param Node $child
     *
     * @return Node
     */
    public function addNode(Node $child)
    {
        $this->children[] = $child;
        $child->setParent($this);

        return $this;
    }

    public function disableChildrenLazyLoading()
    {
        if (is_object($this->children)) {
            $this->children->setInitialized(true);
        }
    }

    /**
     * @param bool $includeOffline
     *
     * @return ArrayCollection
     */
    public function getNodeTranslations($includeOffline = false)
    {
        return $this->nodeTranslations
            ->filter(
            function ($entry) use ($includeOffline) {
                if ($includeOffline || $entry->isOnline()) {
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * @param ArrayCollection $nodeTranslations
     *
     * @return Node
     */
    public function setNodeTranslations($nodeTranslations)
    {
        $this->nodeTranslations = $nodeTranslations;

        return $this;
    }

    /**
     * @param string $lang
     * @param bool   $includeOffline
     *
     * @return NodeTranslation|null
     */
    public function getNodeTranslation($lang, $includeOffline = false)
    {
        $nodeTranslations = $this->getNodeTranslations($includeOffline);
        foreach ($nodeTranslations as $nodeTranslation) {
            if ($lang == $nodeTranslation->getLang()) {
                return $nodeTranslation;
            }
        }

        return null;
    }

    /**
     * Add nodeTranslation
     *
     * @param NodeTranslation $nodeTranslation
     *
     * @return Node
     *
     * @todo Shouldn't we add a check to prevent adding duplicates here?
     */
    public function addNodeTranslation(NodeTranslation $nodeTranslation)
    {
        $this->nodeTranslations[] = $nodeTranslation;
        $nodeTranslation->setNode($this);

        return $this;
    }

    public function disableNodeTranslationsLazyLoading()
    {
        if (is_object($this->nodeTranslations)) {
            $this->nodeTranslations->setInitialized(true);
        }
    }

    /**
     * Set parent
     *
     * @param Node $parent
     *
     * @return Node
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return Node[]
     */
    public function getParents()
    {
        $parent  = $this->getParent();
        $parents = array();
        while ($parent != null) {
            $parents[] = $parent;
            $parent    = $parent->getParent();
        }

        return array_reverse($parents);
    }

    /**
     * @param int $sequenceNumber
     *
     * @return Node
     */
    public function setSequenceNumber($sequenceNumber)
    {
        $this->sequenceNumber = $sequenceNumber;

        return $this;
    }

    /**
     * @return int
     */
    public function getSequenceNumber()
    {
        return $this->sequenceNumber;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param bool $deleted
     *
     * @return Node
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Set referenced entity
     *
     * @param AbstractEntity $entity
     *
     * @return Node
     */
    public function setRef(AbstractEntity $entity)
    {
        $this->setRefEntityName(ClassLookup::getClass($entity));

        return $this;
    }

    /**
     * Set class name of referenced entity
     *
     * @param string $refEntityName
     */
    protected function setRefEntityName($refEntityName)
    {
        $this->refEntityName = $refEntityName;
    }

    /**
     * Get class name of referenced entity
     *
     * @return string
     */
    public function getRefEntityName()
    {
        return $this->refEntityName;
    }

    /**
     * Set internal name
     *
     * @param string $internalName
     *
     * @return Node
     */
    public function setInternalName($internalName)
    {
        $this->internalName = $internalName;

        return $this;
    }

    /**
     * Get internal name
     *
     * @return string
     */
    public function getInternalName()
    {
        return $this->internalName;
    }

    /**
     * @return NodeAdminType
     */
    public function getDefaultAdminType()
    {
        return new NodeAdminType();
    }

    /**
     * @ORM\PrePersist
     */
    public function preInsert()
    {
        if (!$this->sequenceNumber) {
            $parent = $this->getParent();
            if ($parent) {
                $count                = $parent->getChildren()->count();
                $this->sequenceNumber = $count + 1;
            } else {
                $this->sequenceNumber = 1;
            }
        }
    }

    public function __toString()
    {
        return "node " . $this->getId() . ", refEntityName: " . $this->getRefEntityName();
    }
}
