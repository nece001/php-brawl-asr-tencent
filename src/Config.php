<?php

namespace Nece\Brawl\Asr\Tencent;

use Nece\Brawl\Base\Tencent\TencentConfigAbstract;

class Config extends TencentConfigAbstract
{
    /**
     * 引擎模型类型
     *
     * @var array
     * @Author nece001@163.com
     * @DateTime 2023-07-30
     */
    private $EngSerViceType = array(
        '8k_zh' => '中文电话通用',
        '8k_en' => '英文电话通用',
        '16k_zh' => '中文通用',
        '16k_zh-PY' => '中英粤',
        '16k_zh_medical' => '中文医疗',
        '16k_en' => '英语',
        '16k_yue' => '粤语',
        '16k_ja' => '日语',
        '16k_ko' => '韩语',
        '16k_vi' => '越南语',
        '16k_ms' => '马来语',
        '16k_id' => '印度尼西亚语',
        '16k_fil' => '菲律宾语',
        '16k_th' => '泰语',
        '16k_pt' => '葡萄牙语',
        '16k_tr' => '土耳其语',
        '16k_ar' => '阿拉伯语',
        '16k_es' => '西班牙语',
        '16k_zh_dialect' => '多方言'
    );

    /**
     * 识别结果返回形式
     *
     * @var array
     * @Author nece001@163.com
     * @DateTime 2023-07-30
     */
    private $ResTextFormat = array(
        '识别结果文本', '词级别粒度的详细识别结果', '词级别粒度的详细识别结果', '对识别结果按照语义分段【增值付费功能】'
    );

    /**
     * 构建配置模板
     *
     * @Author nece001@163.com
     * @DateTime 2023-07-30
     *
     * @return void
     */
    public function buildTemplate()
    {
        // API参数
        $this->addTemplate(true, 'EngSerViceType', '引擎模型类型', '可识别的语音种类', '', $this->EngSerViceType);
        $this->addTemplate(false, 'WordInfo', '词级时间戳', '0：不显示；1：显示，不包含标点时间戳，2：显示，包含标点时间戳。默认值为 0。', '0');
        $this->addTemplate(false, 'FilterDirty', '是否过滤脏词', '0：不过滤脏词；1：过滤脏词；2：将脏词替换为 * 。默认值为 0。', '0');
        $this->addTemplate(false, 'FilterModal', '是否过语气词', '0：不过滤语气词；1：部分过滤；2：严格过滤 。默认值为 0。', '0');
        $this->addTemplate(false, 'FilterPunc', '是否过滤标点符号', '0：不过滤，1：过滤句末标点，2：过滤所有标点。默认值为 0。', '0');
        $this->addTemplate(false, 'ConvertNumMode', '是否进行阿拉伯数字智能转换', '0：不转换，直接输出中文数字，1：根据场景智能转换为阿拉伯数字。默认值为1。', '0');
        $this->addTemplate(false, 'HotwordId', '热词id', '用于调用对应的热词表，如果在调用语音识别服务时，不进行单独的热词id设置，自动生效默认热词');
        $this->addTemplate(false, 'CustomizationId', '自学习模型 id', '');
        $this->addTemplate(false, 'ReinforceHotword', '热词增强功能', '1:开启后（仅支持8k_zh,16k_zh），将开启同音替换功能，同音字、词在热词中配置。');

        $this->addTemplate(true, 'ResTextFormat', '识别结果返回形式', '[录音文件识别请求参数]', '', $this->ResTextFormat);
        $this->addTemplate(true, 'ChannelNum', '识别声道数', '1：单声道；2：双声道。[录音文件识别请求参数]');
        $this->addTemplate(false, 'SpeakerDiarization', '是否开启说话人分离', '0：不开启，1：开启。[录音文件识别请求参数]');
        $this->addTemplate(false, 'SpeakerNumber', '是否开启说话人分离', '取值范围：0-10，0代表自动分离（目前仅支持≤6个人），1-10代表指定说话人数分离。默认值为 0。[录音文件识别请求参数]');
        $this->addTemplate(false, 'CallbackUrl', '回调 URL', '接收识别结果的服务URL。[录音文件识别请求参数]');
        $this->addTemplate(false, 'EmotionalEnergy', '情绪能量值', '取值范围：[1,10]。值越高情绪越强烈。0:不开启，1:开启。[录音文件识别请求参数]');
        $this->addTemplate(false, 'SentenceMaxLength', '单行最大字数', '取值范围：[6，40]。默认为0，不开启该功能。[录音文件识别请求参数]');
        $this->addTemplate(false, 'EmotionRecognition', '情绪识别能力', '默认为0，不开启。 1：开启情绪识别但是不会在文本展示“情绪标签”， 2：开启情绪识别并且在文本展示“情绪标签”。(增值服务)[录音文件识别请求参数]');

        // 公共参数

        // 证书模板
        $this->buildCredentialTemplate();
        $this->buildClientTemplate();
        $this->buildHttpTemplate();
    }
}
