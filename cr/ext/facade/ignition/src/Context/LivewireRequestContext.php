<?php

namespace Facade\Ignition\Context;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Livewire\LivewireManager;

class LivewireRequestContext extends LaravelRequestContext
{
    /** @var \Livewire\LivewireManager */
    protected $livewireManager;

    public function __construct(
        Request $request,
        LivewireManager $livewireManager
    ) {
        parent::__construct($request);

        $this->livewireManager = $livewireManager;
    }

    public function getRequest()/*: array*/
    {
        $properties = parent::getRequest();

        $properties['method'] = $this->livewireManager->originalMethod();
        $properties['url'] = $this->livewireManager->originalUrl();

        return $properties;
    }

    public function toArray()/*: array*/
    {
        $properties = parent::toArray();

        $properties['livewire'] = $this->getLiveWireInformation();

        return $properties;
    }

    protected function getLiveWireInformation()/*: array*/
    {
        $componentId = $this->request->input('fingerprint.id');
        $componentAlias = $this->request->input('fingerprint.name');

        if ($componentAlias === null) {
            return [];
        }

        try {
            $componentClass = $this->livewireManager->getClass($componentAlias);
        } catch (Exception $e) {
            $componentClass = null;
        }

        return [
            'component_class' => $componentClass,
            'component_alias' => $componentAlias,
            'component_id' => $componentId,
            'data' => $this->resolveData(),
            'updates' => $this->resolveUpdates(),
        ];
    }

    protected function resolveData()/*: array*/
    {
		$memoData = $this->request->input('serverMemo.data');
        $data = isset($memoData) ? $memoData : [];

		$memoDataMeta = $this->request->input('serverMemo.dataMeta');
        $dataMeta = isset($memoDataMeta) ? $memoDataMeta : [];

        foreach (isset($dataMeta['modelCollections']) ? $dataMeta['modelCollections'] : [] as $key => $value) {
            $data[$key] = array_merge(isset($data[$key]) ? $data[$key] : [], $value);
        }

        foreach (isset($dataMeta['models']) ? $dataMeta['models'] : [] as $key => $value) {
            $data[$key] = array_merge(isset($data[$key]) ? $data[$key] : [], $value);
        }

        return $data;
    }

    protected function resolveUpdates()
    {
    	$inputUpdates = $this->request->input('updates');
        $updates = isset($inputUpdates) ? $inputUpdates : [];

        return array_map(function (array $update) {
            $update['payload'] = Arr::except(isset($update['payload']) ? $update['payload'] : [], ['id']);

            return $update;
        }, $updates);
    }
}