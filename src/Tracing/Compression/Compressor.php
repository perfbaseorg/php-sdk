<?php

namespace Perfbase\SDK\Tracing\Compression;

class Compressor
{
    /**
     * Glossary of atomic tokens (class names, method names, etc.) mapped to an integer ID. We only store them once.
     * @var array<int, string>
     */
    public array $glossary = [];

    /**
     * Glossary map.
     * @var array<string, int>
     */
    public array $glossaryMap = [];

    /**
     * The next glossary ID to use.
     * @var int
     */
    public int $nextGlossaryId = 0;

    /**
     * The root node. It has an array of children (keyed "c"), each child has:
     * 'k' => int[] (array of glossary IDs for this segment),
     * 'c' => array of children,
     * 'v' => value if it's a leaf
     * @var array<mixed>
     */
    public array $map = ['c' => []];

    /**
     * Return the glossary ID for a given string token.
     * @param string $token
     * @return int
     */
    private function getGlossaryId(string $token): int
    {
        if (isset($this->glossaryMap[$token])) {
            return $this->glossaryMap[$token];
        }
        $id = $this->nextGlossaryId++;
        $this->glossary[$id] = $token;
        $this->glossaryMap[$token] = $id;
        return $id;
    }

    /**
     * Insert a single key => value into the trie.
     * This method has a recursive function call, so phpstan gets really confused.
     * @param array<string,mixed> $node Current trie node
     * @param array<array<int>> $segmentsOfSegments Each element = array of integer IDs for that segment
     * @param array<int|string> $value
     */
    private function trieInsert(array &$node, array $segmentsOfSegments, array $value): void
    {
        // If we’ve consumed all segments, store the value here
        if (!$segmentsOfSegments) {
            $node['v'] = $value;
            return;
        }

        // Grab the next segment (array of integer token IDs)
        $currentSeg = array_shift($segmentsOfSegments);

        // If no children yet, add an empty array
        if (!isset($node['c'])) {
            $node['c'] = [];
        }
        // Child array. We'll see if there's a child with the same `k`
        // @phpstan-ignore-next-line
        foreach ($node['c'] as &$child) {
            // @phpstan-ignore-next-line
            if (isset($child['k']) && $child['k'] === $currentSeg) {
                // Found a match; recurse
                // @phpstan-ignore-next-line
                $this->trieInsert($child, $segmentsOfSegments, $value);
                return;
            }
        }
        // No child with these tokens => create a new child
        $newChild = [
            'k' => $currentSeg,     // array of integers
            'c' => []
        ];

        // @phpstan-ignore-next-line
        $node['c'][] = $newChild;

        // Insert value deeper
        // @phpstan-ignore-next-line
        $this->trieInsert($node['c'][count($node['c']) - 1], $segmentsOfSegments, $value);
    }

    /**
     * Compress the data.
     * @param array<string, array<int|string>> $data
     * @return array<mixed>
     */
    public function execute(array $data): array
    {
        /** Build the trie from all data */
        foreach ($data as $longKey => $val) {
            // 1) Split on '~' => segments
            $segments = explode('~', $longKey);

            // 2) For each segment, split on '::', map each token to a glossary ID
            $segmentsOfSegments = [];
            foreach ($segments as $segment) {
                $subParts = explode('::', $segment);
                $subPartIds = array_map([$this, 'getGlossaryId'], $subParts);
                $segmentsOfSegments[] = $subPartIds;
            }

            // 3) Insert into trie
            // @phpstan-ignore-next-line
            $this->trieInsert($this->map, $segmentsOfSegments, $val);
        }

        return [
            'compressor' => 'trie',
            'data' => [
                'glossary' => $this->glossary,
                'map' => $this->map
            ]
        ];
    }
}