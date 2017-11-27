<?php

namespace minewar\bow;

use pocketmine\level\Explosion;
use pocketmine\level\particle\FlameParticle;
use pocketmine\entity\Arrow as Projectile;

class Arrow extends Projectile{

public function onUpdate($currentTick){
if($this->closed){
return;
}
if($this->onGround || $this->hadCollision){
$explosion = new Explosion($this, 1);
$explosion->explodeB();
$this->close();
}else{
/*$radio = 0.5;
for($i = 0; $i < 10; $i+=0.2){
$x = $radio*cos($i); //oh friendly lagg xD
$z = $radio*sin($i);
$this->getLevel()->addParticle(new FlameParticle($this->add($x, 0, $z)));
}*/
$this->getLevel()->addParticle(new FlameParticle($this));
}
parent::onUpdate($currentTick);
}

}