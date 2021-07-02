<?php
declare(strict_types=1);

namespace BitrixManticore\Services\Search;

use Bitrix\Main\Context;
use \Bitrix\Main\Loader;
use Manticoresearch\Client as ManticoreClient;
use Manticoresearch\Search as ManticoreSearch;
use Manticoresearch\Index as ManticoreIndex;

class Search
{

	private $indexManticore;
	private $searchManticore;
	private $error;
	static $indexName;
	static $defaultIndexName = 'bitrix';
	static $addStep = 500;
	static $client;

	static $indexFields = array(
	  'pid'=>['type'=>'int'],
	  'skuid'=>['type'=>'int'],
	  'title'=>['type'=>'text'],
	  'content'=>['type'=>'text'],
	);

	static $attributes = array(
	  'morphology' => 'stem_enru',
	  'min_infix_len' => '3',
	);

	function __construct(string $index = null)
	{
		$config = [
		  'host' => '127.0.0.1',
		  'port' => 9308,
		  //'host' => $_ENV['SEARCH_HOST'],
		  //'port' => $_ENV['SEARCH_PORT'],
		  //'transport' => empty($_SERVER['TRANSPORT']) ? 'Http' : $_SERVER['TRANSPORT']
		];

        self::$client = new ManticoreClient($config);
		self::$indexName = ($index) ? $index : self::$defaultIndexName;

		$this->indexManticore  = new ManticoreIndex(self::$client);
		$this->searchManticore = new ManticoreSearch(self::$client);
		$this->indexManticore->setName(self::$indexName);
		$this->searchManticore->setIndex(self::$indexName);
	}

	public function setIndex(string $indexName) {
		self::$indexName = $indexName;
		$this->indexManticore->setName(self::$indexName);
		$this->searchManticore->setIndex(self::$indexName);
	}

	public function checkIndex(string $indexName) : bool
    {
		$this->indexManticore->setName($indexName);

		try{
			$this->indexManticore->describe();
			$this->indexManticore->setName(self::$indexName);
			return true;
		} catch (\Exception $e) {
			$this->indexManticore->setName(self::$indexName);
			//Logger::error(__CLASS__ . ':' . $e->getMessage());
			$this->error = $e->getMessage();
			return false;
		}
	}

	public function createIndex(string $indexName, array $attributes = null) : bool
	{
		try{
			$additionalFields = $this->getPropertiesList($indexName);
			$this->indexManticore->setName($indexName);
			$this->indexManticore->create(($additionalFields) ? self::$indexFields + $additionalFields :self::$indexFields, self::$attributes);
			$this->indexManticore->setName(self::$indexName);
			return true;
		} catch(\Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
	}

	public function dropIndex(string $indexName) : bool
	{
		try{
			$this->indexManticore->setName($indexName);
			$this->indexManticore->drop();
			$this->indexManticore->setName(self::$indexName);
			return true;
		} catch(\Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
	}

	public function reindex(string $indexName = null)
	{
		try {
			if($indexName){
				try{
					$this->checkIndex($indexName);
					$this->setIndex($indexName);
					$this->indexManticore->describe();
				}
				catch (\Exception $e){
					$this->error = $e->getMessage();
					return false;
				}
			}
			else{
				try{
					$this->indexManticore->describe();
				}
				catch (\Exception $e){
					$this->error = $e->getMessage();
					return false;
				}
			}

			$searchebleContent = $this->getBitrixData();
			$this->clearIndex();

			$i = 1;
            $allElementsQty = count($searchebleContent);
			foreach ($searchebleContent as $id => $searchebleItem) {
				$pieceArray[$id] = $searchebleItem;
				if(($i % self::$addStep) == 0 || $allElementsQty == $i) {
                    $this->indexManticore->addDocuments($pieceArray);
					unset($pieceArray);
					//break;
				}
				$i++;
			}
			//Logger::error(__CLASS__ . ':' . $e->getMessage());
			return true;
		} catch(\Exception $e) {
			$this->error = $e->getMessage();
			return false;
		}
	}

	public function clearIndex(string $indexName = null) : bool
    {
        try{
            if($indexName) {
                $this->setIndex($indexName);
                $this->indexManticore->describe();
            }
            $this->indexManticore->truncate();
            return true;
        } catch(\Exception $e) {
            $this->error = $e->getMessage();
            //Logger::error(__CLASS__ . ':' . $e->getMessage());
            return false;
        }
    }

	public function getError()
	{
		return $this->error;
	}

	public function query(string $query, int $limit = 100)
	{

		try {
			$results = $this->searchManticore->limit($limit)->phrase($query)->get();
		} catch(\Exception $e) {
			echo $e->getMessage();
			$this->error = $e->getMessage();
			//Logger::error(__CLASS__ . ':' . $e->getMessage());
			return false;
		}

		foreach($results as $doc) {

			foreach($doc->getData() as $field => $value)
			{
				if($field == 'pid'){
					$response[] = $value;
				}
			}
		}

		if(empty($response)) {
            $query = $this->ConvertKeyboard($query);
            $this->searchManticore = new ManticoreSearch(self::$client);
            $this->searchManticore->setIndex(self::$indexName);
            $results = $this->searchManticore->limit($limit)->phrase($query)->get();
            foreach($results as $doc) {
                foreach($doc->getData() as $field => $value)
                {
                    if($field == 'pid'){
                        $response[] = $value;
                    }
                }
            }
        }

		return $response;
	}

	public function titleQuery(string $query, int $limit = 10)
	{
		if(mb_strlen($query) < 2){
			return false;
		}
		try{
			$results = $this->searchManticore->limit($limit)->match(['query' => $query.'*', 'operator' => 'and'], 'title')->get();
			//$results = $this->searchManticore->limit($limit)->match('@title '.$query.'*')->get();
			foreach($results as $doc)
			{
				foreach($doc->getData() as $field=>$value)
				{
					$response[$doc->getId()][$field] = $value;
				}
			}
		} catch(\Exception $e) {
			$this->error = $e->getMessage();
			//Logger::error(__CLASS__ . ':' . $e->getMessage());
			$response = false;
		}
		return $response;
	}

	private function ConvertKeyboard(string $query): string
    {
        if (Loader::includeModule('search')) {
            $queryArray = explode(' ', $query);
            foreach ($queryArray as $word) {
                $arLang = \CSearchLanguage::GuessLanguage($word);
                if(is_array($arLang) && $arLang["from"] != $arLang["to"]){
                    $query = \CSearchLanguage::ConvertKeyboardLayout($word, $arLang["from"], $arLang["to"]);
                    $resultQuery[] = $query;
                }
                else{
                    $resultQuery[] = $word;
                }
            }
            return implode($resultQuery, ' ');
        }
    }

	private function getPropertiesList(string $indexName){
		if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {
			//$allowedTypes = ['catalog', 'news'];
			$iblocksDB    = \CIBlock::GetList(["SORT" => "ASC"], [
			  'TYPE'    => $allowedTypes,
			  'SITE_ID' => Context::getCurrent()->getSite(),
			  'ACTIVE'  => 'Y',
			], true, false);
			while ($iblock = $iblocksDB->Fetch()) {
                if($iblock['INDEX_ELEMENT'] != 'Y' && $iblock['INDEX_SECTION'] != 'Y') { continue; }
				$iblocks[$iblock['ID']] = $iblock;
				$properties             = \CIBlockProperty::GetList([
				  "sort" => "asc",
				  "name" => "asc",
				], ["ACTIVE" => "Y", "IBLOCK_ID" => $iblock['ID'], 'SEARCHABLE' => 'Y']);
				while ($prop_fields = $properties->GetNext()) {
					if($prop_fields['PROPERTY_TYPE'] == 'N'){
						$arSelectProperties[mb_strtolower('PROPERTY_' . $prop_fields['ID'])] = ['type'=>'int'];
					}
					else{
						$arSelectProperties[mb_strtolower('PROPERTY_' . $prop_fields['ID'])] = ['type'=>'text'];
					}
				}
			}
			return $arSelectProperties;
		}
	}

	private function getBitrixData() {

		foreach ($this->indexManticore->describe() as $fieldName => $fieldType){
			$indexFields[$fieldName] = '';
		}
		unset($indexFields['id']);

		if (Loader::includeModule('iblock') && Loader::includeModule('catalog')) {

			//$allowedTypes = ['catalog', 'news'];
			$iblocksDB    = \CIBlock::GetList(["SORT" => "ASC"], [
			  'TYPE'    => $allowedTypes,
			  'SITE_ID' => Context::getCurrent()->getSite(),
			  'ACTIVE'  => 'Y',
			], true, false);
			while ($iblock = $iblocksDB->Fetch()) {
                if($iblock['INDEX_ELEMENT'] != 'Y' && $iblock['INDEX_SECTION'] != 'Y') { continue; }

				$iblocks[$iblock['ID']] = $iblock;
				$properties             = \CIBlockProperty::GetList([
				  "sort" => "asc",
				  "name" => "asc",
				], ["ACTIVE" => "Y", "IBLOCK_ID" => $iblock['ID'], 'SEARCHABLE' => 'Y']);

				while ($prop_fields = $properties->GetNext()) {
					$arSelectProperties['PROPERTY_' . $prop_fields['ID']] = 'PROPERTY_' . $prop_fields['ID'];
				}
				$arSelect = ["ID", "NAME", "PROPERTY_CML2_LINK", "SEARCHABLE_CONTENT"];
				if ($arSelectProperties) {
					$arSelect += $arSelectProperties;
					unset($arSelectProperties);
				}

				$result = \CIBlockElement::GetList(["ID" => "ASC"], [
				  'IBLOCK_ID' => $iblock['ID'],
				  'ACTIVE'    => 'Y',
				], false, [], $arSelect);

				global $elemProps;
				while ($arelement = $result->Fetch()) {

					$searchebleContent[$arelement['ID']] = $indexFields;
					$searchebleContent[$arelement['ID']]['skuid'] = $arelement['ID'];
					$searchebleContent[$arelement['ID']]['pid'] = ($arelement['PROPERTY_CML2_LINK_VALUE']) ?
					  $arelement['PROPERTY_CML2_LINK_VALUE'] :
					  $arelement['ID'];
					$searchebleContent[$arelement['ID']]['title'] = str_replace('&quot;', "", $arelement['NAME']);
					$searchebleContent[$arelement['ID']]['content'] = str_replace('&quot;', "", $arelement['SEARCHABLE_CONTENT']);

					array_walk($arelement, function (&$value, $key) {
						if ($value) {
							global $elemProps;
							$isMatched = preg_match_all('/^PROPERTY_(\d+)_VALUE$/', $key, $matches);
							if ($isMatched) {
								//$elemProps[mb_strtolower($key)] = $value;
								$elemProps['property_'.$matches[1][0]] = $value;
							}
						}
					});

					if ($elemProps && is_array($elemProps)) {
						foreach ($elemProps as $k => $value){
							$searchebleContent[$arelement['ID']][$k] = $value;
						}
					}
					unset($elemProps);

				}

			}
		}

		return $searchebleContent;
	}

}