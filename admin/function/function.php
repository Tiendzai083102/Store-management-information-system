<?php

class FPNode {
    public $item;
    public $count;
    public $parent;
    public $children;
    public $link;

    public function __construct($item, $count, $parent) {
        $this->item = $item;
        $this->count = $count;
        $this->parent = $parent;
        $this->children = [];
        $this->link = null;
    }

    public function addChild($item) {
        if (!isset($this->children[$item])) {
            $child = new FPNode($item, 0, $this);
            $this->children[$item] = $child;
            return $child;
        }
        return $this->children[$item];
    }
}

class FPTree {
    public $root;
    public $headerTable;

    public function __construct() {
        $this->root = new FPNode(null, 1, null);
        $this->headerTable = [];
    }

    public function addTransaction($transaction, $count = 1) {
        $currentNode = $this->root;
        foreach ($transaction as $item) {
            $currentNode = $currentNode->addChild($item);
            $currentNode->count += $count;

            if (!isset($this->headerTable[$item])) {
                $this->headerTable[$item] = $currentNode;
            } else {
                $lastNode = $this->headerTable[$item];
                while ($lastNode->link != null) {
                    $lastNode = $lastNode->link;
                }
                $lastNode->link = $currentNode;
            }
        }
    }
}

function createFPTree($transactions, $minSupport) {
    // Đếm tần suất các mục
    $itemCounts = [];
    foreach ($transactions as $transaction) {
        foreach ($transaction as $item) {
            if (!isset($itemCounts[$item])) {
                $itemCounts[$item] = 0;
            }
            $itemCounts[$item]++;
        }
    }

    // Loại bỏ các mục không đáp ứng ngưỡng tối thiểu
    $itemCounts = array_filter($itemCounts, function($count) use ($minSupport) {
        return $count >= $minSupport;
    });

    // Sắp xếp các mục theo tần suất giảm dần
    arsort($itemCounts);

    // Xây dựng FP-Tree
    $tree = new FPTree();
    foreach ($transactions as $transaction) {
        $filteredTransaction = array_filter($transaction, function($item) use ($itemCounts) {
            return isset($itemCounts[$item]);
        });

        usort($filteredTransaction, function($a, $b) use ($itemCounts) {
            return $itemCounts[$b] <=> $itemCounts[$a];
        });

        if (!empty($filteredTransaction)) {
            $tree->addTransaction($filteredTransaction);
        }
    }

    return $tree;
}

function fpgrowth($tree, $minSupport, $prefix = []) {
    $patterns = [];
    $items = array_keys($tree->headerTable);

    foreach ($items as $item) {
        $newPrefix = array_merge($prefix, [$item]);
        $patterns[] = $newPrefix;

        $conditionalPatternBase = [];
        $node = $tree->headerTable[$item];

        while ($node != null) {
            $path = [];
            $parent = $node->parent;
            while ($parent->item != null) {
                $path[] = $parent->item;
                $parent = $parent->parent;
            }
            for ($i = 0; $i < $node->count; $i++) {
                $conditionalPatternBase[] = $path;
            }
            $node = $node->link;
        }

        $conditionalTree = createFPTree($conditionalPatternBase, $minSupport);
        if (!empty($conditionalTree->root->children)) {
            $patterns = array_merge($patterns, fpgrowth($conditionalTree, $minSupport, $newPrefix));
        }
    }

    return $patterns;
}

function convertOrdersToTransactions($orders) {
    $transactions = [];
    foreach ($orders as $order) {
        $orderId = $order['order_id'];
        $product = $order['product'];
        
        if (!isset($transactions[$orderId])) {
            $transactions[$orderId] = [];
        }
        
        $transactions[$orderId][] = $product;
    }
    
    return array_values($transactions);
}

function findItemCombos($patterns, $comboSize) {
    $itemCombos = [];
    foreach ($patterns as $pattern) {
        if (count($pattern) >= $comboSize) {
            $combos = combinations($pattern, $comboSize);
            foreach ($combos as $combo) {
                sort($combo); // Sắp xếp để tránh các combo trùng lặp
                $itemCombos[implode(',', $combo)] = $combo;
            }
        }
    }
    return array_values($itemCombos);
}

// Hàm sinh các tổ hợp kích thước $comboSize từ một mảng $items
function combinations($items, $comboSize, $start = 0, $currentCombo = [], &$combos = []) {
    if (count($currentCombo) == $comboSize) {
        $combos[] = $currentCombo;
        return;
    }
    for ($i = $start; $i < count($items); $i++) {
        $currentCombo[] = $items[$i];
        combinations($items, $comboSize, $i + 1, $currentCombo, $combos);
        array_pop($currentCombo);
    }
    return $combos;
}