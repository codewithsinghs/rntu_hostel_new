<?php // Code within app\Helpers\Helper.php
namespace App\Helpers;
use Config;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Guest;
use App\Models\Resident;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;


// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
// use Illuminate\Support\Facades\DateTime;
// use Illuminate\Support\Facades\DateTimeZone;

class Helper
{
    public static function clean($string) {

      $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

      return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    public static function generate_token() {
      return Helper::clean(Hash::make(rand() . time() . rand()));
    }

    public static function generate_token_expiry() {
        return time() + 24*3600*1;  // 365 days
    }

    public static function get_auth_guest_user(Request $request){

        $user = Guest::find($request->header('auth-id'));
        return $user;

    }

    public static function get_auth_admin_user(Request $request){

        $user = User::find($request->header('auth-id'));
        return $user;

    }

    public static function get_resident_details($user_id){

        $resident = Resident::Where('user_id',$user_id)->get()->first();
        return $resident;
    }


    public static function is_token_valid($guest_id, $token, &$error) {
        // Log::info("Checking token for guest_id: $guest_id with token: $token");
        if ($row = Guest::where('id', $guest_id)->where('token', $token)->first()) {
            
        if ($row->token_expiry > time()) {
                // Token is valid
                $error = NULL;
                return $row;
            } else {
                $error = array('success' => 0, 'message' => Helper::error_message(103), 'error_code' => 2);
                return FALSE;
            }
        }
        $error = array('success' => 0, 'message' => Helper::error_message(104), 'error_code' => 2);     // error code = 2 means invalid token
        return FALSE;
    }
    public static function is_token_valid_admin($admin_id, $token, &$error) {
        // Log::info("Checking token for guest_id: $guest_id with token: $token");
        if ($row = User::where('id', $admin_id)->where('token', $token)->first()) {
            
        if ($row->token_expiry > time()) {
                // Token is valid
                $error = NULL;
                return $row;
            } else {
                $error = array('success' => 0, 'message' => Helper::error_message(103), 'error_code' => 2);
                return FALSE;
            }
        }
        $error = array('success' => 0, 'message' => Helper::error_message(104), 'error_code' => 2);     // error code = 2 means invalid token
        return FALSE;
    }

        // Convert all NULL values to empty strings
    public static function null_safe($arr) {
        $newArr = array();
        foreach ($arr as $key => $value) {
            $newArr[$key] = ($value == NULL && $value != 0) ? "" : $value;
        }
        return $newArr;
    }
    
    public static function error_message($code) {

      switch($code) {
        case 101:
            $string = trans('pages.invalid_input');
            break;
        case 102:
            $string = trans('pages.email_already_use');
            break;
        case 103:
            $string = trans('pages.token_expired');
            break;
        case 104:
            $string = trans('pages.invalid_token');
            break;
        case 105:
            $string = trans('pages.invalid_email_password');
            break;
        case 106:
            $string = trans('pages.current_password_wrong');
            break;
        case 107:
            $string = trans('pages.password_not_match');
            break;
        case 108:
            $string = trans('pages.mail_configure_error');
            break;
        case 109:
            $string = trans('pages.no_result_found');
            break;
        case 110: 
            $string = trans('pages.user_account_delete_success');
            break;
        case 111:
            $string = trans('pages.mail_not_found');
            break;
        case 112:
            $string = trans('pages.password_not_match');
            break;
        case 113:
            $string = trans('pages.failed_to_upload');
            break;
        case 114:
            $string = trans('pages.invalid_mobile');
            break;
        case 115:
            $string = trans('pages.phone_already_use');
            break;
          default:
              $string = trans('pages.unknown_error');
      }
      return $string;
  
  }
  public static function success_message($code) {

      switch($code) {
          case 200:
              $string = "Success";
              break;
          case 202:
              $string = trans('pages.login_success');
              break;
          case 203:
              $string = trans('pages.logout_success');
              break;
          case 204:
              $string = trans('pages.register_success');
              break;
          case 205:
              $string = trans('pages.mail_sent_success');
              break;
          case 206;
              $string = trans('pages.request_create_success');
              break;
          case 207;
              $string = trans('pages.request_cancel_success');
              break;
          case 208:
              $string = trans('pages.request_accepted');
              break;
          case 209:
              $string = trans('pages.provider_started');
              break;
          case 210:
              $string = trans('pages.provider_arrived');
              break;
          case 211:
              $string = trans('pages.service_started');
              break;
          case 212:
              $string = trans('pages.service_completed');
              break;
          case 213:
              $string = trans('pages.service_rating_done');
              break;
          case 214:
              $string = trans('pages.request_provider_assigned_success');
              break;
          case 215:
              $string = trans('pages.request_user_reject_success');
              break;
          case 216:
              $string = trans('pages.register_success');
              break;
          case 217:
              $string = trans('pages.password_change_success');
              break;
          case 218:
              $string = trans('pages.profile_updated');
              break;
          case 219:
              $string = trans('pages.user_account_delete_success');
              break;
          case 220:
              $string = trans('pages.service_updated_success');
              break;
          case 221:
              $string = trans('pages.available_updated');
              break;
          case 222:
              $string = trans('pages.bidding_sent_success');
              break;
          case 223:
              $string = trans('pages.action_success');
              break;
          case 224;
              $string = trans('pages.card_delete_success');
              break;
          case 225;
              $string = trans('pages.card_default_success');
              break;
          case 226:
              $string = trans('pages.request_rejected');
              break;
          case 227:
              $string = trans('pages.request_payment_success');
              break;
          case 228:
              $string = trans('pages.payment_mode_change_success');
              break;
          case 229:
              $string = trans('pages.request_payment_confirm_success');
              break;
          case 230;
              $string = trans('pages.request_bidding_cancel_success');
              break;
          case 231;
              $string = trans('pages.location_updated');
              break;
          case 232;
              $string = trans('pages.push_provider_confirm_request_message');
              break;
                  
          default:
              $string = "";
      }
      return $string;
  
    }




}