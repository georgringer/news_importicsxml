<?php

namespace GeorgRinger\NewsImporticsxml\Hooks\Backend\Element;

/**
 * This file is part of the "news_importicsxml" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Core\Utility\DebugUtility;

class JsonElement extends AbstractNode
{
    public function render()
    {
        $parameterArray = $this->data['parameterArray'];
        $resultArray = $this->initializeResultArray();

        $json = $parameterArray['itemFormElValue'];
        if (!empty($json)) {
            $data = json_decode($json, true);
            if (\is_array($data) && !empty($data)) {
                $resultArray['html'] = DebugUtility::viewArray($data);
            }
        }

        return $resultArray;
    }
}
