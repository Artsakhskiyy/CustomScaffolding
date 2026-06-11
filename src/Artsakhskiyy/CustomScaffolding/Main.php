<?php

declare(strict_types=1);

namespace Artsakhskiyy\CustomScaffolding;

use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\data\bedrock\block\convert\BlockStateReader;
use pocketmine\data\bedrock\block\convert\BlockStateWriter;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\StringToItemParser;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use pocketmine\world\format\io\GlobalBlockStateHandlers;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use Artsakhskiyy\CustomScaffolding\block\ScaffoldingBlock;

class Main extends PluginBase {

    protected function onEnable(): void {
        $this->registerScaffolding();
    }

    private function registerScaffolding(): void {
        $this->registerOnCurrentThread();

        $pool = $this->getServer()->getAsyncPool();
        $pool->addWorkerStartHook(function(int $worker) use ($pool): void {
            $pool->submitTaskToWorker(new class extends AsyncTask {
                public function onRun(): void {
                    $block = ScaffoldingBlock::getInstance();
                    RuntimeBlockStateRegistry::getInstance()->register($block);

                    GlobalBlockStateHandlers::getDeserializer()->map(BlockTypeNames::SCAFFOLDING, function(BlockStateReader $data) use ($block): ScaffoldingBlock {
                        $b = clone $block;
                        $b->setStability($data->readInt("stability"));
                        $b->setStabilityCheck($data->readBool("stability_check"));
                        return $b;
                    });

                    GlobalBlockStateHandlers::getSerializer()->map($block, function(ScaffoldingBlock $block): BlockStateWriter {
                        return BlockStateWriter::create(BlockTypeNames::SCAFFOLDING)
                            ->writeInt("stability", $block->getStability())
                            ->writeBool("stability_check", $block->isStabilityCheck());
                    });
                }
            }, $worker);
        });

        CreativeInventory::getInstance()->add(clone ScaffoldingBlock::getInstance()->asItem());
    }

    private function registerOnCurrentThread(): void {
        $block = ScaffoldingBlock::getInstance();

        RuntimeBlockStateRegistry::getInstance()->register($block);

        GlobalBlockStateHandlers::getDeserializer()->map(BlockTypeNames::SCAFFOLDING, function(BlockStateReader $data) use ($block): ScaffoldingBlock {
            $b = clone $block;
            $b->setStability($data->readInt("stability"));
            $b->setStabilityCheck($data->readBool("stability_check"));
            return $b;
        });

        GlobalBlockStateHandlers::getSerializer()->map($block, function(ScaffoldingBlock $block): BlockStateWriter {
            return BlockStateWriter::create(BlockTypeNames::SCAFFOLDING)
                ->writeInt("stability", $block->getStability())
                ->writeBool("stability_check", $block->isStabilityCheck());
        });

        GlobalItemDataHandlers::getDeserializer()->map(
            BlockTypeNames::SCAFFOLDING,
            fn() => clone $block->asItem()
        );

        GlobalItemDataHandlers::getSerializer()->map(
            $block->asItem(),
            fn() => new SavedItemData(BlockTypeNames::SCAFFOLDING)
        );

        StringToItemParser::getInstance()->register("scaffolding", fn() => clone $block->asItem());
    }
}
