<?php
/**
 * yansongda/pay支付库配置文件.
 * User: Administrator
 * Date: 2018/9/26
 * Time: 17:01
 */
return [
  'alipay'=>[
      'app_id'=>'2016092200570468',
      'ali_public_key'=>'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA+0qzdftKZ2xpdM90udNbKDlrn62FVSYTJAZiQ+tavZsou5jUGx+xNajpcqsoW9FV6ps9NskkOCXl5BzVsyIaBZihnD2GIdpAEBN6NZh+nazPuX/jgM4x57+0WZST9O0iCQ1JaB4kPb6esuI3Hu/B8LNsr9YtYut2fxmvyN68p53dFQ/mnt3TCgZy2CNpFlT6ta0MuygCGOBqMENjFJqu/aDPfgvRgQP0/V1dZCONpVrFqx8lNypUrJyCybKgeIqUFSx5zeqX7me5qJalbaLORJQjHqBZ8oPkmJ6QMq6m9+slz/Zbg5nRv3SUN9+uJ7V7Zbr/PK9hDxdgNaUIG0QAxwIDAQAB',
      'private_key'=>'MIIEogIBAAKCAQEAzExBL1k016Ju3d5Q3k3jfq62lImaNVy1G+VpVzJo2cyyhZxV/vLxnHK1JU9PkJoU8Vs0gOFgc7vJFpFloDW7xBvU6sKJt8JfYSDL4qVQDDo/L4OY575BYk7LH2M4lobUFfLRbNUzGDYaA/iy/uPkKPZtacFnpubfltoaOe1yk2gIuQmxm0viUvmEpqZryF25W2t2ysiGpKPfcK2YhCnnMkPn4Yjc1jiCUn5IpXlVZvdjoPFshNg+K40HntNgftZoSRbryESwcPBrEEQ3UNTX6oSYlXsJHSlKXE6E+nmRVCGR+syKWE+G+zOALmfWMer/fvL6WzawH6VZJDcgI3lSIQIDAQABAoIBAG1JNW2QuNXJTKlfLb9dxx6Tc5QN7/Ivv6pGlI1SqAgqmi0jLlWNvHXGXuSwgo2F/0IFWha/eYsvnyh1avnDBMipYsKagnVMgx1AIBEEAcgouHhAW2FMw0lFgR8vQqwzP+zl0eX4Prbq6gvJ1GbJndnTyT/TeBEuW9kknvbx8GKtRNHoPxecYYEDSal9WXJbQXb9xRRKg6OqoOTcVTBtGQDYxMFp5VYEh8YsYRH3FMNy251Kg2oM8aQxonTtYx3mhrOBSxxIlmtaXhNiTXlnI8GQAwl4YhVvSat9pM7UerySFfPiRkDs7knVyXcCufy3Ji/nX7k7FuXZyHMYE98rEgECgYEA+Annuko+7s1ZY0zXzRjKUBzVF5kCjDupd4YXPl0ZJ9czvCSpiwazwAItW5xvAgWbqmYAWypBL/kBxf4nOI4yduQxJbxsRAna4nmKiUpuzm5VCd5Q3mNjxXmPSmgfkB+AAnF28ohzYU+wqAt2tS9sl4ypivItS4ZY+utSmCKSyhECgYEA0trwIPCqZJBxCnXIHTSlTYJMgJ+WKfmlpxWAJzT+8uyVMLssaRrvwI5at2QVNxo1ZNhYvTMJ74jYstYrX9YMZ1L+HVb1N9eL73L8U+7LHrq1cirwEu+9QnHcN16vwAiBWq732Lg4r6k7dpaMd+UsrGOxaAuXjQvKplyGBPMMdxECgYAIJgMn2pKQs8xQ99BLG5ph4WCawtsTkk4x8ATJdrOB3I8FikrLl2/GSgwFa3E2JssDYRB37j5v/gUx5PSS5hEAWOTIcZU48v3u3MFRW1GilHtUtKoBdFBtx12Ouzb2PCKvcdzPgO4Rb1XFX4MJ1sIBhIx5VRVp2sMHkz2GY/RgkQKBgEzTtyDZb8xakMWV6scxcnWOX1+SRj2fF8uMWvhuJ+LNbwKlgGX/iZHojIc7sTs1knTfG148pYcgnoxy2rT9oeFX7P01vP2OzQ7/H49Zd6sHrqsdmAHeVmBKaknGd1UKBE/NZsMRRJ5ElET+T8ozt5ZMcMyPRLYAsOLy3WYaRNVxAoGAZGtD1FXD1nreQLnAOJNeBVfpxQSRf+9703/nvnx2kspdaFhFOUjW+2gh0Y24rV18bnju5oPNRg8U+JugHkLRxfFR/fLeLu1k/7HoStPihLhpg7/w0MAsdG5ysWmjBCCcVhRmpevTN7TzzoVmNwD/71e4HCN4gOyywOLlE7K30WY=',
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