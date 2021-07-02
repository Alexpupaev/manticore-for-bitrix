<?

namespace App\Controllers;

use Bitrix\Main\HttpRequest;
use App\Services\Search\Search;
use App\Services\Routing\Response;

class SearchController {

	private $searchService;

	function __construct(Search $searchService)
	{
		$this->searchService = $searchService;
	}

	public function check(HttpRequest $request)
	{
		$indexName = (string)$request->getPost('index');
		$checkFlag = $this->searchService->checkIndex($indexName);
		if($checkFlag)
		{
			$response = Response::Json(true, 'Индекс существует');
		}
		else{
			$response = Response::Json(false, [], ['message' => $this->searchService->getError()]);
		}

		echo $response;
	}

	public function create(HttpRequest $request)
	{
		$indexName = (string)$request->getPost('index');
		$createFlag = $this->searchService->createIndex($indexName);
		if($createFlag)
		{
			$response = Response::Json(true, 'Индекс успешно создан');
		}
		else{
			$response = Response::Json(false, [], ['message' => $this->searchService->getError()]);
		}

		echo $response;
	}

	public function drop(HttpRequest $request)
	{
		$indexName = (string)$request->getPost('index');

		$dropFlag = $this->searchService->dropIndex($indexName);
		if($dropFlag)
		{
			$response = Response::Json(true, 'Индекс удален');
		}
		else{
			$response = Response::Json(false, [], ['message' => $this->searchService->getError()]);
		}

		echo $response;
	}

	public function reindex(HttpRequest $request)
	{
		$indexName = (string)$request->getPost('index');

		$indexFlag = $this->searchService->reindex($indexName);
		if($indexFlag)
		{
			$response = Response::Json(true, 'Переиндексация успешно выполнена');
		}
		else{
			$response = Response::Json(false, [], ['message' => $this->searchService->getError()]);
		}

		echo $response;
	}

	public function clear(HttpRequest $request)
    {
        $indexName = (string)$request->getPost('index');

        $indexFlag = $this->searchService->clearIndex($indexName);
        if($indexFlag)
        {
            $response = Response::Json(true, 'Индекс успешно очищен');
        }
        else{
            $response = Response::Json(false, [], ['message' => $this->searchService->getError()]);
        }

        echo $response;
    }

	public function query(HttpRequest $request)
	{
		$indexName = $request->getPost('index');
		$query = $request->getPost('query');
		$this->searchService->setIndex($indexName);
		dump($this->searchService->query($query));
	}

	public function title(HttpRequest $request)
	{
		$indexName = $request->getPost('index');
		$query = $request->getPost('query');
		$this->searchService->setIndex($indexName);
		dump($this->searchService->titleQuery($query));
	}

	public function update(HttpRequest $request)
	{

	}

}