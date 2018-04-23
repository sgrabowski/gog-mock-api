<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CartRepository")
 */
class Cart
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $currency;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CartProduct", mappedBy="cart", orphanRemoval=true, cascade={"persist"} )
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return Collection|CartProduct[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function updateProduct(CartProduct $cartProduct): self
    {
        $criteria = Criteria::create()->where(Criteria::expr()->eq("productId", $cartProduct->getProductId()));
        $existing = $this->products->matching($criteria);

        if(count($existing) > 0) {
            foreach ($existing as $product) {
                $product->setQuantity($cartProduct->getQuantity());
            }
        } else {
            $this->products->add($cartProduct);
            $cartProduct->setCart($this);
        }

        return $this;
    }

    public function removeProduct(CartProduct $cartProduct): self
    {
        if ($this->products->contains($cartProduct)) {
            $this->products->removeElement($cartProduct);
            // set the owning side to null (unless already changed)
            if ($cartProduct->getCart() === $this) {
                $cartProduct->setCart(null);
            }
        }

        return $this;
    }
}
