<?php

declare(strict_types=1);

namespace Artsakhskiyy\CustomScaffolding;

use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\block\BlockStateNames;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use Artsakhskiyy\CustomScaffolding\block\ScaffoldingBlock;

class Main extends PluginBase {

    protected function onEnable(): void {
        $this->registerScaffolding();
    }

    private function registerScaffolding(): void {

        $block = ScaffoldingBlock::getInstance();

        $serializer = GlobalBlockStateHandlers::getSerializer();
        $deserializer = GlobalBlockStateHandlers::getDeserializer();

        $deserializer->map(BlockTypeNames::SCAFFOLDING, function(BlockStateReader $reader) use ($block): ScaffoldingBlock {
            return (clone $block)
                ->setStability($reader->readBoundedInt(BlockStateNames::STABILITY, 0, 7))
                ->setStabilityCheck($reader->readBool(BlockStateNames::STABILITY_CHECK));
        });

        $serializer->map($block, function(ScaffoldingBlock $block): BlockStateWriter {
            return BlockStateWriter::create(BlockTypeNames::SCAFFOLDING)
                ->writeInt(BlockStateNames::STABILITY, $block->getStability())
                ->writeBool(BlockStateNames::STABILITY_CHECK, $block->isStabilityCheck());
        });

        RuntimeBlockStateRegistry::getInstance()->register($block);

        GlobalItemDataHandlers::getDeserializer()->map(
            BlockTypeNames::SCAFFOLDING,
            fn() => clone $block->asItem()
        );

        GlobalItemDataHandlers::getSerializer()->map(
            $block->asItem(),
            fn() => new SavedItemData(BlockTypeNames::SCAFFOLDING)
        );

        StringToItemParser::getInstance()->register("scaffolding", fn() => clone $block->asItem());
        CreativeInventory::getInstance()->add(clone $block->asItem());
    }
}
