<?php

namespace Nece\Brawl\Asr\Tencent;

use Nece\Brawl\Asr\AsrInterface;
use Nece\Brawl\Asr\AsrResult;
use Nece\Brawl\Asr\AsrTaskResult;
use Nece\Brawl\Asr\DescribeTaskResult;
use Nece\Brawl\Base\Tencent\TencentClientAbstract;
use TencentCloud\Asr\V20190614\AsrClient;
use TencentCloud\Asr\V20190614\Models\CreateRecTaskRequest;
use TencentCloud\Asr\V20190614\Models\DescribeTaskStatusRequest;
use TencentCloud\Asr\V20190614\Models\SentenceRecognitionRequest;

class Asr extends TencentClientAbstract implements AsrInterface
{

    private $client;

    /**
     * 获取客户端
     *
     * @Author nece001@163.com
     * @DateTime 2023-07-30
     *
     * @return AsrClient
     */
    private function getClient()
    {
        if (!$this->client) {

            $credential = $this->buildCredential();
            $client_profile = $this->buildClientProfile();
            $this->client = new AsrClient($credential, '', $client_profile);
        }

        return $this->client;
    }

    /**
     * 语音数据短语音识别（一句话识别）
     *
     * @Author nece001@163.com
     * @DateTime 2023-07-30
     *
     * @param string $data
     * @param string $format
     *
     * @return AsrResult
     */
    public function shortAudioFromData(string $data, string $format, string $HotwordList = '', bool $InputSampleRate = false): AsrResult
    {
        return $this->shortAudio($format, $data, 1, $HotwordList,  $InputSampleRate);
    }

    /**
     * 语音 URL短语音识别（一句话识别）
     *
     * @Author nece001@163.com
     * @DateTime 2023-07-30
     *
     * @param string $audio_url 语音文件url
     * @param string $HotwordList 临时热词，热词规则：“热词|权重”
     * @param boolean $InputSampleRate 支持pcm格式的8k音频在与引擎采样率不匹配的情况下升采样到16k后识别
     *
     * @return AsrResult
     */
    public function shortAudioFromUrl(string $audio_url, string $HotwordList = '', bool $InputSampleRate = false): AsrResult
    {
        $format = substr(strrchr($audio_url, '.'), 1);
        return $this->shortAudio($format, $audio_url, 0, $HotwordList,  $InputSampleRate);
    }

    /**
     * 一句话识别
     * 文档：https://cloud.tencent.com/document/api/1093/35646
     *
     * @Author nece001@163.com
     * @DateTime 2023-07-30
     *
     * @param string $format 识别音频的音频格式，支持wav、pcm、ogg-opus、speex、silk、mp3、m4a、aac、amr。
     * @param string $input 语音的URL地址|语音数据
     * @param integer $SourceType 语音数据来源。0：语音 URL；1：语音数据（post body）。
     * @param string $HotwordList 临时热词，热词规则：“热词|权重”
     * @param boolean $InputSampleRate 支持pcm格式的8k音频在与引擎采样率不匹配的情况下升采样到16k后识别
     *
     * @return AsrResult
     */
    private function shortAudio(string $format, string $input, int $SourceType, string $HotwordList = '', bool $InputSampleRate = false): AsrResult
    {
        $EngSerViceType = $this->getConfigValue('EngSerViceType', '16k_zh');
        $WordInfo = intval($this->getConfigValue('WordInfo', ''));
        $FilterDirty = intval($this->getConfigValue('FilterDirty', ''));
        $FilterModal = intval($this->getConfigValue('FilterModal', ''));
        $FilterPunc = intval($this->getConfigValue('FilterPunc', ''));
        $ConvertNumMode = intval($this->getConfigValue('ConvertNumMode', ''));
        $HotwordId = $this->getConfigValue('HotwordId', '');
        $CustomizationId = $this->getConfigValue('CustomizationId', '');
        $ReinforceHotword = $this->getConfigValue('ReinforceHotword', '');

        $req = new SentenceRecognitionRequest();
        $req->setEngSerViceType($EngSerViceType);
        $req->setSourceType($SourceType);
        $req->setVoiceFormat($format);
        if ($SourceType == 0) {
            $req->setUrl($input);
        } else {
            $req->setDataLen(strlen($input));
            $req->setData(base64_encode($input));
        }

        if ($WordInfo) {
            $req->setWordInfo($WordInfo);
        }
        if ($FilterDirty) {
            $req->setFilterDirty($FilterDirty);
        }
        if ($FilterModal) {
            $req->setFilterModal($FilterModal);
        }
        if ($FilterPunc) {
            $req->setFilterPunc($FilterPunc);
        }
        if ($ConvertNumMode) {
            $req->setConvertNumMode($ConvertNumMode);
        }
        if ($HotwordId != '') {
            $req->setHotwordId($HotwordId);
        }
        if ($CustomizationId != '') {
            $req->setCustomizationId($CustomizationId);
        }
        if ($ReinforceHotword != '') {
            $req->setReinforceHotword($ReinforceHotword);
        }
        if ($HotwordList != '') {
            $req->setHotwordList($HotwordList);
        }
        if ($ReinforceHotword != '') {
            $req->setReinforceHotword($ReinforceHotword);
        }
        if ($InputSampleRate) {
            $req->setInputSampleRate(1);
        }

        $result = new AsrResult();
        $res = $this->getClient()->SentenceRecognition($req);
        $result->setRaw($res->toJsonString());
        $data = $res->serialize();

        // 格式化结果
        if (isset($data['Result'])) {

            $result->setSuccess();
            $result->setRequestId($data['RequestId']);
            $result->setText($data['Result']);
            $result->setDuration($data['AudioDuration']);
            if (isset($data['WordList']) && $data['WordList']) {
                foreach ($data['WordList'] as $row) {
                    $result->addWord($row['Word'], $row['StartTime'], $row['EndTime']);
                }
            }
        }

        return $result;
    }

    /**
     * 创建识别任务（录音文件识别请求）
     *
     * @Author nece001@163.com
     * @DateTime 2023-07-30
     *
     * @param string $audio_url
     *
     * @return AsrTaskResult 任务标识ID
     */
    public function createTask(string $audio_url): AsrTaskResult
    {
        $EngSerViceType = $this->getConfigValue('EngSerViceType', '16k_zh');
        $ChannelNum = intval($this->getConfigValue('ChannelNum', 1, false));
        $ResTextFormat = intval($this->getConfigValue('ResTextFormat', 1, false));
        $SpeakerDiarization = $this->getConfigValue('SpeakerDiarization', '');
        $SpeakerNumber = $this->getConfigValue('SpeakerNumber', '');
        $CallbackUrl = $this->getConfigValue('CallbackUrl', '');

        $FilterDirty = intval($this->getConfigValue('FilterDirty', 0));
        $FilterModal = intval($this->getConfigValue('FilterModal', 0));
        $FilterPunc = intval($this->getConfigValue('FilterPunc', 0));
        $ConvertNumMode = intval($this->getConfigValue('ConvertNumMode', 0));
        $HotwordId = $this->getConfigValue('HotwordId', '');
        $CustomizationId = $this->getConfigValue('CustomizationId', '');
        $ReinforceHotword = $this->getConfigValue('ReinforceHotword', '');

        $EmotionalEnergy = intval($this->getConfigValue('EmotionalEnergy', 0));
        $SentenceMaxLength = intval($this->getConfigValue('SentenceMaxLength', 0));
        $EmotionRecognition = intval($this->getConfigValue('EmotionRecognition', 0));

        $req = new CreateRecTaskRequest();
        $req->setEngineModelType($EngSerViceType);
        $req->setSourceType(0);
        $req->setUrl($audio_url);
        $req->setChannelNum($ChannelNum);
        $req->setResTextFormat($ResTextFormat);

        if ($SpeakerDiarization != '') {
            $req->setSpeakerDiarization($SpeakerDiarization);
        }
        if ($SpeakerNumber != '') {
            $req->setSpeakerNumber($SpeakerNumber);
        }
        if ($CallbackUrl != '') {
            $req->setCallbackUrl($CallbackUrl);
        }
        if ($FilterDirty) {
            $req->setFilterDirty($FilterDirty);
        }
        if ($FilterModal) {
            $req->setFilterModal($FilterModal);
        }
        if ($FilterPunc) {
            $req->setFilterPunc($FilterPunc);
        }
        if ($ConvertNumMode) {
            $req->setConvertNumMode($ConvertNumMode);
        }
        if ($HotwordId != '') {
            $req->setHotwordId($HotwordId);
        }
        if ($CustomizationId != '') {
            $req->setCustomizationId($CustomizationId);
        }
        if ($ReinforceHotword != '') {
            $req->setReinforceHotword($ReinforceHotword);
        }
        if ($ReinforceHotword != '') {
            $req->setReinforceHotword($ReinforceHotword);
        }

        if ($EmotionalEnergy) {
            $req->setEmotionalEnergy($EmotionalEnergy);
        }
        if ($SentenceMaxLength) {
            $req->setSentenceMaxLength($SentenceMaxLength);
        }
        if ($EmotionRecognition) {
            $req->setEmotionRecognition($EmotionRecognition);
        }

        $result = new AsrTaskResult();
        $res = $this->getClient()->CreateRecTask($req);
        $result->setRaw($res->toJsonString());

        // 格式化结果
        $data = $res->serialize();
        if (isset($data['Data']['TaskId'])) {

            $result->getSuccess();
            $result->setRequestId($data['RequestId']);
            $result->setTaskId($data['Data']['TaskId']);
        }

        return $result;
    }

    /**
     * 识别任务结果查询
     *
     * @Author nece001@163.com
     * @DateTime 2023-07-30
     *
     * @param string $task_id
     *
     * @return DescribeTaskResult
     */
    public function describeTask($task_id): DescribeTaskResult
    {
        $req = new DescribeTaskStatusRequest();
        $req->setTaskId(intval($task_id));

        $result = new DescribeTaskResult();
        $res = $this->getClient()->DescribeTaskStatus($req);
        $result->setRaw($req->toJsonString());
        $data = $res->serialize();

        if (isset($data['Data'])) {

            $result->setSuccess();
            $result->setRequestId($data['RequestId']);
            $result->setTaskId($data['Data']['TaskId']);
            $result->setStatus($data['Data']['Status']);
            $result->setDuration($data['Data']['AudioDuration']);
            $result->setResult($data['Data']['Result']);
            $result->setError($data['Data']['ErrorMsg']);

            if ($data['Data']['ResultDetail']) {
                foreach ($data['Data']['ResultDetail'] as $detail) {
                    $words = array();
                    if (isset($detail['Words']) && $detail['Words']) {
                        foreach ($detail['Words'] as $word) {
                            $words[] = $result->buildWordItem($word['Word'], $word['OffsetStartMs'], $word['OffsetEndMs']);
                        }
                    }

                    $FinalSentence = isset($detail['FinalSentence']) ? $detail['FinalSentence'] : '';
                    $SliceSentence = isset($detail['SliceSentence']) ? $detail['SliceSentence'] : '';
                    $StartMs = isset($detail['StartMs']) ? $detail['StartMs'] : 0;
                    $EndMs = isset($detail['EndMs']) ? $detail['EndMs'] : 0;
                    $SpeechSpeed = isset($detail['SpeechSpeed']) ? $detail['SpeechSpeed'] : 0;
                    $SpeakerId = isset($detail['SpeakerId']) ? $detail['SpeakerId'] : '';
                    $SilenceTime = isset($detail['SilenceTime']) ? $detail['SilenceTime'] : 0;
                    $EmotionalEnergy = isset($detail['EmotionalEnergy']) ? $detail['EmotionalEnergy'] : 0;
                    $EmotionType = isset($detail['EmotionType']) ? $detail['EmotionType'] : array();

                    $result->addDetail($FinalSentence,  $SliceSentence,  $StartMs,  $EndMs,  $SpeechSpeed,  $SpeakerId,  $SilenceTime,  $words,  $EmotionalEnergy, $EmotionType);
                }
            }
        }

        return $result;
    }
}
