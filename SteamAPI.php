<?php
function makeSteamAPIRequest($url, $method = "GET", $params = []){
   $queryData = [];
   foreach($params as $paramName => $param){
      if ( is_array($param) ){
         $queryParam = [];
         $i = 0;
         foreach($param as $entry)
            $queryParam[] = $paramName."[".($i++)."]=".$entry;
         $queryData[] = implode("&", $queryParam);
         unset($i);
      } else {
         $queryData[] = $paramName."=".$param;
      }
   }

   if ( substr($url, 0, 1) != "/" )
      $url = "/".$url;

   $url = "https://api.steampowered.com" . $url;

   if ( $method == "GET" && $queryData != "" ){
      $url .= "?".implode("&", $queryData);
   }

   $curl = curl_init($url);


   curl_setopt($curl, CURLOPT_VERBOSE, true);
   $verbose = fopen('php://temp', 'w+');
   curl_setopt($curl, CURLOPT_STDERR, $verbose);

   curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
   if ( $method == "POST" ){
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, implode("&", $queryData));
   }
   $res = curl_exec($curl);

   if ($res === FALSE) {
      echo "URL: {$url}\n";
      var_dump($queryData);
      printf("cUrl error (#%d): %s<br>\n", curl_errno($curl), htmlspecialchars(curl_error($curl)));
      rewind($verbose);
      $verboseLog = stream_get_contents($verbose);
      echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
      die();
   }
   curl_close($curl);

   return json_decode($res);
}



# ====================================[ Defines ]====================================

define("k_EResultOK", 1); // Success.
define("k_EResultFail", 2); // Generic failure.
define("k_EResultNoConnection", 3); // Your Steam client doesn't have a connection to the back-end.
define("k_EResultInvalidPassword", 5); // Password/ticket is invalid.
define("k_EResultLoggedInElsewhere", 6); // The user is logged in elsewhere.
define("k_EResultInvalidProtocolVer", 7); // Protocol version is incorrect.
define("k_EResultInvalidParam", 8); // A parameter is incorrect.
define("k_EResultFileNotFound", 9); // File was not found.
define("k_EResultBusy", 10); // Called method is busy - action not taken.
define("k_EResultInvalidState", 11); // Called object was in an invalid state.
define("k_EResultInvalidName", 12); // The name was invalid.
define("k_EResultInvalidEmail", 13); // The email was invalid.
define("k_EResultDuplicateName", 14); // The name is not unique.
define("k_EResultAccessDenied", 15); // Access is denied.
define("k_EResultTimeout", 16); // Operation timed out.
define("k_EResultBanned", 17); // The user is VAC2 banned.
define("k_EResultAccountNotFound", 18); // Account not found.
define("k_EResultInvalidSteamID", 19); // The Steam ID was invalid.
define("k_EResultServiceUnavailable", 20); // The requested service is currently unavailable.
define("k_EResultNotLoggedOn", 21); // The user is not logged on.
define("k_EResultPending", 22); // Request is pending, it may be in process or waiting on third party.
define("k_EResultEncryptionFailure", 23); // Encryption or Decryption failed.
define("k_EResultInsufficientPrivilege", 24); // Insufficient privilege.
define("k_EResultLimitExceeded", 25); // Too much of a good thing.
define("k_EResultRevoked", 26); // Access has been revoked (used for revoked guest passes.)
define("k_EResultExpired", 27); // License/Guest pass the user is trying to access is expired.
define("k_EResultAlreadyRedeemed", 28); // Guest pass has already been redeemed by account, cannot be used again.
define("k_EResultDuplicateRequest", 29); // The request is a duplicate and the action has already occurred in the past, ignored this time.
define("k_EResultAlreadyOwned", 30); // All the games in this guest pass redemption request are already owned by the user.
define("k_EResultIPNotFound", 31); // IP address not found.
define("k_EResultPersistFailed", 32); // Failed to write change to the data store.
define("k_EResultLockingFailed", 33); // Failed to acquire access lock for this operation.
define("k_EResultLogonSessionReplaced", 34); // The logon session has been replaced.
define("k_EResultConnectFailed", 35); // Failed to connect.
define("k_EResultHandshakeFailed", 36); // The authentication handshake has failed.
define("k_EResultIOFailure", 37); // There has been a generic IO failure.
define("k_EResultRemoteDisconnect", 38); // The remote server has disconnected.
define("k_EResultShoppingCartNotFound", 39); // Failed to find the shopping cart requested.
define("k_EResultBlocked", 40); // A user blocked the action.
define("k_EResultIgnored", 41); // The target is ignoring sender.
define("k_EResultNoMatch", 42); // Nothing matching the request found.
define("k_EResultAccountDisabled", 43); // The account is disabled.
define("k_EResultServiceReadOnly", 44); // This service is not accepting content changes right now.
define("k_EResultAccountNotFeatured", 45); // Account doesn't have value, so this feature isn't available.
define("k_EResultAdministratorOK", 46); // Allowed to take this action, but only because requester is admin.
define("k_EResultContentVersion", 47); // A Version mismatch in content transmitted within the Steam protocol.
define("k_EResultTryAnotherCM", 48); // The current CM can't service the user making a request, user should try another.
define("k_EResultPasswordRequiredToKickSession", 49); // You are already logged in elsewhere, this cached credential login has failed.
define("k_EResultAlreadyLoggedInElsewhere", 50); // The user is logged in elsewhere. (Use k_EResultLoggedInElsewhere instead!)
define("k_EResultSuspended", 51); // Long running operation has suspended/paused. (eg. content download.)
define("k_EResultCancelled", 52); // Operation has been canceled, typically by user. (eg. a content download.)
define("k_EResultDataCorruption", 53); // Operation canceled because data is ill formed or unrecoverable.
define("k_EResultDiskFull", 54); // Operation canceled - not enough disk space.
define("k_EResultRemoteCallFailed", 55); // The remote or IPC call has failed.
define("k_EResultPasswordUnset", 56); // Password could not be verified as it's unset server side.
define("k_EResultExternalAccountUnlinked", 57); // External account (PSN, Facebook...) is not linked to a Steam account.
define("k_EResultPSNTicketInvalid", 58); // PSN ticket was invalid.
define("k_EResultExternalAccountAlreadyLinked", 59); // External account (PSN, Facebook...) is already linked to some other account, must explicitly request to replace/delete the link first.
define("k_EResultRemoteFileConflict", 60); // The sync cannot resume due to a conflict between the local and remote files.
define("k_EResultIllegalPassword", 61); // The requested new password is not allowed.
define("k_EResultSameAsPreviousValue", 62); // New value is the same as the old one. This is used for secret question and answer.
define("k_EResultAccountLogonDenied", 63); // Account login denied due to 2nd factor authentication failure.
define("k_EResultCannotUseOldPassword", 64); // The requested new password is not legal.
define("k_EResultInvalidLoginAuthCode", 65); // Account login denied due to auth code invalid.
define("k_EResultAccountLogonDeniedNoMail", 66); // Account login denied due to 2nd factor auth failure - and no mail has been sent.
define("k_EResultHardwareNotCapableOfIPT", 67); // The users hardware does not support Intel's Identity Protection Technology (IPT).
define("k_EResultIPTInitError", 68); // Intel's Identity Protection Technology (IPT) has failed to initialize.
define("k_EResultParentalControlRestricted", 69); // Operation failed due to parental control restrictions for current user.
define("k_EResultFacebookQueryError", 70); // Facebook query returned an error.
define("k_EResultExpiredLoginAuthCode", 71); // Account login denied due to an expired auth code.
define("k_EResultIPLoginRestrictionFailed", 72); // The login failed due to an IP restriction.
define("k_EResultAccountLockedDown", 73); // The current users account is currently locked for use. This is likely due to a hijacking and pending ownership verification.
define("k_EResultAccountLogonDeniedVerifiedEmailRequired", 74); // The logon failed because the accounts email is not verified.
define("k_EResultNoMatchingURL", 75); // There is no URL matching the provided values.
define("k_EResultBadResponse", 76); // Bad Response due to a Parse failure, missing field, etc.
define("k_EResultRequirePasswordReEntry", 77); // The user cannot complete the action until they re-enter their password.
define("k_EResultValueOutOfRange", 78); // The value entered is outside the acceptable range.
define("k_EResultUnexpectedError", 79); // Something happened that we didn't expect to ever happen.
define("k_EResultDisabled", 80); // The requested service has been configured to be unavailable.
define("k_EResultInvalidCEGSubmission", 81); // The files submitted to the CEG server are not valid.
define("k_EResultRestrictedDevice", 82); // The device being used is not allowed to perform this action.
define("k_EResultRegionLocked", 83); // The action could not be complete because it is region restricted.
define("k_EResultRateLimitExceeded", 84); // Temporary rate limit exceeded, try again later, different from k_EResultLimitExceeded which may be permanent.
define("k_EResultAccountLoginDeniedNeedTwoFactor", 85); // Need two-factor code to login.
define("k_EResultItemDeleted", 86); // The thing we're trying to access has been deleted.
define("k_EResultAccountLoginDeniedThrottle", 87); // Login attempt failed, try to throttle response to possible attacker.
define("k_EResultTwoFactorCodeMismatch", 88); // Two factor authentication (Steam Guard) code is incorrect.
define("k_EResultTwoFactorActivationCodeMismatch", 89); // The activation code for two-factor authentication (Steam Guard) didn't match.
define("k_EResultAccountAssociatedToMultiplePartners", 90); // The current account has been associated with multiple partners.
define("k_EResultNotModified", 91); // The data has not been modified.
define("k_EResultNoMobileDevice", 92); // The account does not have a mobile device associated with it.
define("k_EResultTimeNotSynced", 93); // The time presented is out of range or tolerance.
define("k_EResultSmsCodeFailed", 94); // SMS code failure - no match, none pending, etc.
define("k_EResultAccountLimitExceeded", 95); // Too many accounts access this resource.
define("k_EResultAccountActivityLimitExceeded", 96); // Too many changes to this account.
define("k_EResultPhoneActivityLimitExceeded", 97); // Too many changes to this phone.
define("k_EResultRefundToWallet", 98); // Cannot refund to payment method, must use wallet.
define("k_EResultEmailSendFailure", 99); // Cannot send an email.
define("k_EResultNotSettled", 100); // Can't perform operation until payment has settled.
define("k_EResultNeedCaptcha", 101); // The user needs to provide a valid captcha.
define("k_EResultGSLTDenied", 102); // A game server login token owned by this token's owner has been banned.
define("k_EResultGSOwnerDenied", 103); // Game server owner is denied for some other reason such as account locked, community ban, vac ban, missing phone, etc.
define("k_EResultInvalidItemType", 104); // The type of thing we were requested to act on is invalid.
define("k_EResultIPBanned", 105); // The IP address has been banned from taking this action.
define("k_EResultGSLTExpired", 106); // This Game Server Login Token (GSLT) has expired from disuse; it can be reset for use.
define("k_EResultInsufficientFunds", 107); // user doesn't have enough wallet funds to complete the action
define("k_EResultTooManyPending", 108); // There are too many of this thing pending already
