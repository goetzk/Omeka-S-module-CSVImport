<?php
namespace CSVImport\Form;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Form\Element;
use Zend\Form\Form;

class ImportForm extends Form
{
    use EventManagerAwareTrait;

    /**
     * A list of standard delimiters.
     *
     * @var array
     */
    protected $delimiterList = [
        ',' => 'comma', // @translate
        ';' => 'semi-colon', // @translate
        ':' => 'colon', // @translate
        "__>\t<__" => 'tabulation', // @translate
        '__>\r<__' => 'carriage return', // @translate
        '__> <__' => 'space', // @translate
        '|' => 'pipe', // @translate
        // '  ' => 'double space', // @translate
        // '' => 'empty', // @translate
    ];

    /**
     * A list of standard enclosures.
     *
     * @var array
     */
    protected $enclosureList = [
        '"' => 'double quote', // @translate
        "'" => 'quote', // @translate
        '#' => 'hash', // @translate
        // '' => 'empty', // @translate
    ];

    /**
     * @var array
     */
    protected $configCsvImport;

    public function init()
    {
        $inputFilter = $this->getInputFilter();

        $this->add([
            'name' => 'source',
            'type' => Element\File::class,
            'options' => [
                'label' => 'Spreadsheet (csv, tsv or ods)', // @translate
                'info' => 'The CSV, TSV or ODS file to upload. LibreOffice is recommended for compliant formats.', //@translate
            ],
            'attributes' => [
                'id' => 'source',
                'required' => 'true',
            ],
        ]);

        $valueOptions = [];
        foreach ($this->configCsvImport['mappings'] as $key => $mapping) {
            $valueOptions[$key] = $mapping['label'];
        }
        $this->add([
            'name' => 'resource_type',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Import type', // @translate
                'info' => 'The type of data being imported', // @translate
                'value_options' => $valueOptions,
            ],
            'attributes' => [
                'id' => 'resource_type',
                // Set a default value.
                'value' => 'items',
            ],
        ]);

        // TODO Move the specific parameters into the source class.

        // Commenting out code that uses UserSettings in case we want to replace or
        // use them differently later

        $valueOptions = $this->getDelimiterList();
        $this->add([
            'name' => 'delimiter',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'CSV column delimiter', // @translate
                'info' => 'A single character that will be used to separate columns in the csv file.', // @translate
                'value_options' => $valueOptions,
            ],
            'attributes' => [
                'id' => 'delimiter',
            ],
        ]);

        $valueOptions = $this->getEnclosureList();
        $this->add([
            'name' => 'enclosure',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'CSV column enclosure', // @translate
                'info' => 'A single character that will be used to separate columns in the csv file. The enclosure can be omitted when the content does not contain the delimiter.', // @translate
                'value_options' => $valueOptions,
            ],
            'attributes' => [
                'id' => 'enclosure',
            ],
        ]);

        $inputFilter->add([
            'name' => 'source',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'resource_type',
            'required' => true,
        ]);
        $inputFilter->add([
            'name' => 'delimiter',
            'required' => false,
        ]);
        $inputFilter->add([
            'name' => 'enclosure',
            'required' => false,
        ]);

        $addEvent = new Event('form.add_elements', $this);
        $this->getEventManager()->triggerEvent($addEvent);
        $event = new Event('form.add_input_filters', $this, ['inputFilter' => $inputFilter]);
        $this->getEventManager()->triggerEvent($event);
    }

    /**
     * Extract values that can’t be passed via a select form element in Zend.
     *
     * The values is extracted from a string between "__>" and "<__".
     *
     * @param string $value
     * @return string
     */
    public function extractParameter($value)
    {
        if (strpos($value, '__>') === 0
            && ($pos = strpos($value, '<__')) == (strlen($value) - 3)
        ) {
            $result = substr($value, 3, $pos - 3);
            return $result === '\r' ? "\r" : $result;
        }
        return $value;
    }

    /**
     * Integrate values that can’t be passed via a select form element in Zend.
     *
     * The values are integrated with a string between "__>" and "<__".
     *
     * @param string $value
     * @return string
     */
    public function integrateParameter($value)
    {
        $specialValues = ["\r", "\t", ' '];
        return in_array($value, $specialValues, true)
            ? sprintf('__>%s<__', $value)
            : $value;
    }

    public function setConfigCsvImport(array $configCsvImport)
    {
        $this->configCsvImport = $configCsvImport;
    }

    /**
     * Return a list of standard delimiters.
     *
     * @return array
     */
    public function getDelimiterList()
    {
        return $this->delimiterList;
    }

    /**
     * Return a list of standard enclosures.
     *
     * @return array
     */
    public function getEnclosureList()
    {
        return $this->enclosureList;
    }
}
