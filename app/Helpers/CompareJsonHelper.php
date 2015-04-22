<?php

namespace App\Helpers;

use App\CompareDTO;
use App\Exceptions\CompareJsonMissingInfoException;
use Illuminate\Support\Collection;

trait CompareJsonHelper {

    protected $compare_json_state = [];
    protected $compare_collection;

    public function addCompareNode(CompareDTO $compareDTO, $set = 'images_a', $key = false)
    {
        if(!isset($this->compare_json_state[$set]))
        {
            $this->compare_json_state[$set] = new Collection();
        }

        if($key)
        {
            $this->compare_json_state[$set]->put($key, (array) $compareDTO);
        }
        else
        {
            $this->compare_json_state[$set]->push((array) $compareDTO);
        }
    }

    public function removeCompareNode($set = 'images_a', $key = 0)
    {
        if(isset($this->compare_json_state[$set]) && isset($this->compare_json_state[$set][$key]))
        {
            $this->compare_json_state[$set]->forget($key);
        }
    }

    public function updateCompareValue($set = 'images_a', $key = 0, $name = false, $value)
    {
        if(in_array($set, ['project_id', 'stage', 'request_id']))
        {
            $this->compare_json_state[$set] = $value;
        }
        elseif (isset($this->compare_json_state[$set]) && isset($this->compare_json_state[$set][$key]))
        {
            $new_value = $this->compare_json_state[$set]->get($key);
            if($new_value == null)
                return false;

            $new_value[$name] = $value;
            $this->compare_json_state[$set]->put($key, $new_value);
        }
    }

    public function getCompareJsonState()
    {
        $this->reviewCompareJson();
        return $this->compare_json_state;
    }

    protected function reviewCompareJson()
    {

        if(!isset($this->compare_json_state['project_id']))
            throw new CompareJsonMissingInfoException("There is no project id");

        if(!isset($this->compare_json_state['request_id']))
            throw new CompareJsonMissingInfoException("There is no request id");

        if(!isset($this->compare_json_state['stage']))
            $this->compare_json_state['stage'] = 0;

        if(isset($this->compare_json_state['images_a']))
            $this->compare_json_state['images_a'] = $this->flattenButKeepOrder($this->compare_json_state['images_a']);

        if(isset($this->compare_json_state['images_b']))
            $this->compare_json_state['images_b'] = $this->flattenButKeepOrder($this->compare_json_state['images_b']);
    }

    protected function flattenButKeepOrder($items)
    {
        if(count($items) > 0)
        {
            $items = $items->sortBy(function($value) { return $value['original_page'];});
            $results = [];
            foreach($items as $index => $item)
            {
                $results[] = $item;
            }
            return $results;
        } else {
            return $items;
        }
    }

    /**
     * @param array $compare_json_state
     */
    public function setCompareJsonState($compare_json_state)
    {
        $this->compare_json_state = $compare_json_state;
    }

    /**
     * @return mixed
     */
    public function getCompareCollection()
    {
        return new Collection();
    }


    protected function buildDto($result, $original_page = 0)
    {
        return new CompareDTO(
            $path = $result['image_destination'] . $result['image_name'],
            $dirname = $result['image_destination'],
            $custom_name = false,
            $timestamp = false,
            $size = false,
            $type = 'file',
            $basename = $result['image_name'],
            $extension = false,
            $filename = false,
            $url = false,
            $quick_diff = false,
            $original_page
        );
    }


}
