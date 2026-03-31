<?php

declare(strict_types=1);

namespace Artsakhskiyy\CustomScaffolding\block;

use pocketmine\block\BlockTypeInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\Transparent;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class ScaffoldingBlock extends Transparent {

    private int $stability = 0;
    private bool $stabilityCheck = false;

    /** @var ?ScaffoldingBlock */
    private static ?ScaffoldingBlock $instance = null;

    public function __construct(BlockIdentifier $id, string $name, ?BlockTypeInfo $typeInfo = null) {
        parent::__construct(
            $id,
            $name,
            $typeInfo ?? new BlockTypeInfo(BlockBreakInfo::instant())
        );
    }

    public static function getInstance(): ScaffoldingBlock {
        if (self::$instance === null) {
            self::$instance = new self(
                new BlockIdentifier(\pocketmine\block\BlockTypeIds::newId()),
                "Scaffolding",
                new BlockTypeInfo(BlockBreakInfo::instant())
            );
        }
        return self::$instance;
    }

    protected function describeBlockOnlyState(RuntimeDataDescriber $w): void {
        $w->boundedInt(
            "stability",
            0,
            7,
            function(): int {
                return $this->stability;
            },
            function(int $v): void {
                $this->stability = $v;
            }
        );

        $w->bool(
            "stability_check",
            function(): bool {
                return $this->stabilityCheck;
            },
            function(bool $v): void {
                $this->stabilityCheck = $v;
            }
        );
    }

    public function getSupportType(int $facing): SupportType {
        return SupportType::NONE;
    }

    public function getStability(): int {
        return $this->stability;
    }

    public function setStability(int $value): self {
        if ($value < 0 || $value > 7) {
            throw new \InvalidArgumentException("Stability must be 0-7, got {$value}");
        }
        $this->stability = $value;
        return $this;
    }

    public function isStabilityCheck(): bool {
        return $this->stabilityCheck;
    }

    public function setStabilityCheck(bool $value): self {
        $this->stabilityCheck = $value;
        return $this;
    }

    public function onInteract(
        Item $item,
        int $face,
        Vector3 $clickVector,
        ?Player $player = null,
        array &$returnedItems = []
    ): bool {
        if ($player === null) return false;

        $this->stability = ($this->stability + 1) % 8;
        $this->position->getWorld()->setBlock($this->position, $this);

        return true;
    }
}
