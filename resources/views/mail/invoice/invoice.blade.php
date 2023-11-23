<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <!-- [if !mso] <!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <!-- <![endif]-->
    <meta name="viewport" content="width=device-width,user-scalable=no"/>
    <title>{{ __('Invoice') }}</title>
</head>
<body
    style="
        margin: 0;
        padding: 0;
        min-width: 100%;
        background-color: #f9fafb;
        -ms-text-size-adjust: 100%;
        -webkit-text-size-adjust: 100%;
        font-family: Arial, Helvetica, sans-serif;
        color: #272727;
    "
>
<div
    align="center"
    style="
        width: 100%;
        table-layout: fixed;
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
        background-color: #f1f1f1;
        padding: 40px 0;
    "
>
    <table
        border="0"
        cellpadding="0"
        cellspacing="0"
        style="
            margin:0;
            padding:40px 15px;
            max-width: 600px;
            width: 100%;
            background: #ffffff;
            border: 1px solid #e4e4e4;
            font-size: 16px;
        "
    >
        <tbody>
            <tr align="center">
                <td>
                    <div style="font-size: 24px; margin-bottom: 20px;">
                        <span style="color: #6571ff;">DV</span>
                        <span style="color: #000a65;">Pay</span>
                    </div>
                </td>
            </tr>
            <tr align="center">
                <td>
                    <div style="margin-bottom: 10px; font-size: 20px; font-weight: 600;">
                        {{ __('Invoice') }}
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center">
                   <div style="margin-bottom: 20px;"># {{ $invoice['invoiceId']  }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div style="height: 1px; background: #e4e4e4; margin-bottom: 30px"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div style="font-weight: 600; margin-bottom: 20px;">{{ __('Information') }}:</div>
                </td>
            </tr>
            <tr>
                <td>
                    <table border="0" cellpadding="0" cellspacing="0" style="width: 100%;">
                        <tbody>
                            <tr>
                                <td height="30">
                                    {{ __('Created Date') }}:
                                </td>
                                <td height="30">
                                    {{ $invoice['createdAt']  }}
                                </td>
                            </tr>
                            <tr>
                                <td height="30">
                                    {{ __('Expiration Date') }}:
                                </td>
                                <td height="30">
                                    {{ $invoice['expiredAt']  }}
                                </td>
                            </tr>
                            <tr>
                                <td height="30">
                                    {{ __('Amount') }}:
                                </td>
                                <td height="30">
                                    {{ $invoice['amount']  }} $
                                </td>
                            </tr>
                            <tr>
                                <td height="30">
                                    {{ __('Status') }}:
                                </td>
                                <td height="30">
                                    {{ __($invoice['status']) }}
                                </td>
                            </tr>
                            <tr>
                                <td height="30">
                                    {{ __('Store') }}:
                                </td>
                                <td height="30">
                                    <span>{{ $invoice['store']  }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td height="30">
                                    {{ __('Store URL') }}:
                                </td>
                                <td height="30">
                                    <a href="{{ $invoice['storeUrl'] }}" target="_blank" style="text-decoration: none; color: #6571ff;">
                                        {{ $invoice['storeUrl'] }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td height="30">
                                    {{ __('Description') }}:
                                </td>
                                <td height="30">
                                    {{ $invoice['description'] }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <div style="height: 1px; background: #e4e4e4; margin: 30px 0;"></div>
                </td>
            </tr>
            <tr>
                <td>
                    <div style="font-weight: 600; margin-bottom: 20px;">{{ __('Transactions') }}:</div>
                </td>
            </tr>
            @foreach ($transactions as $key)
            <tr>
                <td>
                    <table
                         border="1"
                         cellpadding="2"
                         cellspacing="0"
                         bgcolor="#e9ecef"
                         style="
                            width: 100%;
                            font-size: 14px;
                            border-color: #e4e4e4;
                            margin-bottom: 10px;
                        "
                    >
                        <tbody>
                            <tr>
                                <th height="24" align="left">
                                    <span style="margin-right: 5px;">
                                        {{ __('Currency') }}:
                                    </span>
                                </th>
                                <td height="24" align="left">
                                    <span style="margin-right: 5px;">
                                        {{ $key['currencyId']  }}
                                    </span>
                                </td>
                            </tr>
                             <tr>
                                 <th height="24" align="left">
                                     <span style="margin-right: 5px;">
                                        {{ __('Tx Id') }}:
                                    </span>
                                 </th>
                                 <td height="24" style="word-break: break-all;" align="left">
                                     {{ $key['txId']  }}
                                 </td>
                             </tr>
                             <tr>
                                 <th height="24" align="left">
                                     <span style="margin-right: 10px;">
                                         {{ __('Amount') }}:
                                    </span>
                                 </th>
                                 <td height="24" align="left">
                                     {{ $key['amount']  }} $
                                 </td>
                             </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>
                    <div style="height: 1px; background: #e4e4e4; margin: 30px 0;"></div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <div style="margin-bottom: 10px;">
                        {{ __('Support') }}: {{ $invoice['supportEmail'] }}
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <span style="font-size: 14px;">
                        &copy; DV Pay
                    </span>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>