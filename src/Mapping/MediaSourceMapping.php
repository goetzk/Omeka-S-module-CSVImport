<?php
namespace CSVImport\Mapping;

use Zend\View\Renderer\PhpRenderer;

class MediaSourceMapping extends AbstractMapping
{
    protected $label = 'Media source'; // @translate
    protected $name = 'media-source';

    public function getSidebar(PhpRenderer $view)
    {
        return $view->mediaSourceSidebar();
    }

    public function processRow(array $row)
    {
        // Reset the data and the map between rows.
        $this->setHasErr(false);
        $data = [];

        $this->findResourceFromIdentifier = $this->getServiceLocator()->get('ControllerPluginManager')
            ->get('findResourceFromIdentifier');

        // First, pull in the global settings.
        $resourceType = $this->args['resource_type'];
        $isMedia = $resourceType === 'media';

        // Set columns.
        if (isset($this->args['column-media_source'])) {
            $mediaMap = $this->args['column-media_source'];
        }

        // Set default values.
        if ($isMedia) {
            if (!empty($this->args['o:media']['o:id'])) {
                $data = ['o:id' => (int) $this->args['o:media']['o:id']];
            }
        } else {
            if (!empty($this->args['o:media'])) {
                $data['o:media'] = [];
                foreach ($this->args['o:media'] as $id) {
                    $data['o:media'][] = ['o:id' => (int) $id];
                }
            }
        }

        // Return if no column.
        if (empty($mediaMap)) {
            return $data;
        }

        $config = $this->getServiceLocator()->get('Config');
        $mediaAdapters = $config['csvimport']['media_ingester_adapter'];
        $action = $this->args['action'];

        $multivalueMap = isset($this->args['column-multivalue']) ? $this->args['column-multivalue'] : [];

        foreach ($row as $index => $values) {
            if (isset($mediaMap[$index])) {
                if (array_key_exists($index, $multivalueMap) && strlen($multivalueMap[$index])) {
                    $values = explode($multivalueMap[$index], $values);
                    $values = array_map('trim', $values);
                    if ($isMedia) {
                        array_splice($values, 1);
                    }
                } else {
                    $values = [$values];
                }

                $ingester = $mediaMap[$index];
                $values = array_filter($values, 'strlen');
                foreach ($values as $mediaDatum) {
                    $mediaDatumJson = [
                        'o:ingester' => $ingester,
                        'o:source' => $mediaDatum,
                    ];
                    if (isset($mediaAdapters[$ingester])) {
                        $adapter = new $mediaAdapters[$ingester];
                        $mediaDatumJson = array_merge($mediaDatumJson, $adapter->getJson($mediaDatum));
                    }

                    // Don't add to array, because the default media may be set.
                    if ($isMedia) {
                        return $mediaDatumJson;
                    }
                    $data[] = $mediaDatumJson;
                }
            }
        }

        return ['o:media' => $data];
    }
}
