<?php
/**
 * yansongda/pay支付库配置文件.
 * User: Administrator
 * Date: 2018/9/26
 * Time: 17:01
 */
return [
  'alipay'=>[
      'app_id'=>'',
      'ali_public_key'=>'',
      'private_key'=>'',
      'log'=>[
          'file'=>storage_path('logs/alipay.log'),
      ],
  ],
   'wechat'=>[
       'app_id'=>'',
       'mch_id'=>'',
       'key'=>'',
       'cert_client'=>'',
       'cert_key'=>'',
       'log'=>[
           'file'=>storage_path('logs/wechat_pay.log'),
       ],
   ],
];