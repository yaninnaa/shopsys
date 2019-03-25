<?php

namespace Shopsys\FrameworkBundle\Model\Product\Search;

class ProductElasticsearchConverter
{

     private $random = 0;
    /**
     * @param string $index
     * @param array $data
     * @return array
     */
    public function convertBulk(string $index, array $data): array
    {
        $result = [];
        foreach ($data as $id => $row) {
            $result[] = [
                'index' => [
                    '_index' => $index,
                    '_type' => '_doc',
                    '_id' => (string)$id,
                ],
            ];
            $row['price'] = ['cost' => 1500, 'pricingGroupId' => 1];
            $row['in_stock'] = true;
            $row['brand_id'] = 5;
            $row['flags'] = ['id' => 3];
            if ($this->random === 0) {
                $row['parameters'][] = ['parameter_id' => 6, 'parameter_value_id' => 5];
                $row['parameters'][] = ['parameter_id' => 4, 'parameter_value_id' => 5];
                $this->random = 1;
            } elseif ($this->random === 1) {
                $row['parameters'] = ['parameter_id' => 4, 'parameter_value_id' => 5];
                $row['parameters'] = ['parameter_id' => 3, 'parameter_value_id' => 3];
                $this->random = 2;
            } else {
                $row['parameters'] = ['parameter_id' => 4, 'parameter_value_id' => 5];
                $row['parameters'] = ['parameter_id' => 6, 'parameter_value_id' => 3];
                $this->random = 0;
            }
            $result[] = $row;
        }
        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    public function convertExportBulk(array $data): array
    {
        $result = [];
        foreach ($data as $row) {
            $id = (string)$row['id'];
            unset($row['id']);
            $result[$id] = $row;
        }

        return $result;
    }

    /**
     * @param array $data
     * @return int[]
     */
    public function extractIds(array $data): array
    {
        return array_column($data, 'id');
    }
}
