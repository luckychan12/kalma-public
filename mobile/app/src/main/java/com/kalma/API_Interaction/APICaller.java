package com.kalma.API_Interaction;

import android.content.Context;
import android.content.SharedPreferences;
import android.util.Log;

import com.android.volley.AuthFailureError;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.kalma.R;

import org.joda.time.DateTime;
import org.joda.time.DateTimeZone;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.util.HashMap;
import java.util.Map;


public class APICaller {
    //set application context to use getResource methods
    private Context context;
    public APICaller(Context context){
        this.context = context;
    }

    public void loginRefresh(final ServerCallback callback){
        SharedPreferences settings = context.getSharedPreferences("TOKENS", 0);
        String token = settings.getString("RefreshToken", "");
        if (token == ""){
            return;
        }
        refreshTokens(token, callback);
    }

    public void checkTokenExpiry(){
        DateTime dateTimeGMT = new DateTime();
        long now = dateTimeGMT.getMillis() / 1000;
        String token = AuthStrings.getInstance(context).getRefreshToken();
        if(AuthStrings.getInstance(context).getAuthExp() > now){
            Log.i("refreshed token", "refresh not needed");
            return;
        }


        Log.e("refresh token", token);

        ServerCallback callback = new ServerCallback() {
            @Override
            public void onSuccess(JSONObject result) {
                Log.i("refreshed token", "true");
            }

            @Override
            public void onFail(VolleyError error) {

            }
        };

        refreshTokens(token, callback);
    }



    private void refreshTokens(final String token, final ServerCallback callback) {
        String location = context.getResources().getString(R.string.api_refresh);
        JSONObject content = new JSONObject();
        try {
            content.put("refresh_token", token);
            content.put("client_fingerprint", AuthStrings.getInstance(context).getDeviceToken());
            Log.e("TOKEN", token);

        } catch (JSONException e) {
           e.printStackTrace();
        }
        RequestQueue requestQueue = RequestQueueSingleton.getInstance(context.getApplicationContext()).getRequestQueue();
        try {
            String url = context.getResources().getString(R.string.api_url) + location;
            //create request
            JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(Request.Method.POST, url, content,
                    new Response.Listener<JSONObject>() {
                        @Override
                        public void onResponse(JSONObject result) {
                            try {
                                AuthStrings authStrings = AuthStrings.getInstance(context);
                                JSONObject responseBody = result;
                                String accessToken = responseBody.getString("access_token");
                                int accessExp = Integer.parseInt(responseBody.getString("access_expiry"));
                                authStrings.setAuthToken(accessToken, accessExp);
                                String refreshToken = responseBody.getString("refresh_token");
                                int refreshExp = Integer.parseInt(responseBody.getString("refresh_expiry"));
                                authStrings.setRefreshToken(refreshToken, refreshExp);
                                callback.onSuccess(result);
                            }
                            catch (JSONException je) {

                                Log.e("JSONException", "onErrorResponse: ", je);
                            }
                        }
                    },
                    new Response.ErrorListener() {
                        @Override
                        public void onErrorResponse(VolleyError error ) {
                            try {
                                //retrieve error message and display
                                String jsonInput = new String(error.networkResponse.data, "utf-8");
                                JSONObject responseBody = new JSONObject(jsonInput);
                                String message = responseBody.getString("message");
                                AuthStrings.getInstance(context).forgetRefreshToken();
                                Log.w("Error.Response", jsonInput);

                            } catch (JSONException je) {
                                Log.e("JSONException", "onErrorResponse: ", je);
                            } catch (UnsupportedEncodingException err) {
                                Log.e("EncodingError", "onErrorResponse: ", err);
                            }
                            callback.onFail(error);
                        }
                    }){
                @Override
                public Map getHeaders() throws AuthFailureError {
                    return new HashMap<String, String>();
                }
            };
            //add request to queue to be sent to API
            requestQueue.add(jsonObjectRequest);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }


    public void post(Boolean requiresAuth, final JSONObject content, final Map headers, String location, final ServerCallback callback) {
        if (requiresAuth) {
            checkTokenExpiry();
        }
        RequestQueue requestQueue = RequestQueueSingleton.getInstance(context.getApplicationContext()).getRequestQueue();
        try {
            String url = context.getResources().getString(R.string.api_url) + location;
            //create request
            JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(Request.Method.POST, url, content,
                    new Response.Listener<JSONObject>() {
                        @Override
                        public void onResponse(JSONObject response) {
                            callback.onSuccess(response);
                        }
                    },
                    new Response.ErrorListener() {
                        @Override
                        public void onErrorResponse(VolleyError error ) {
                            callback.onFail(error);
                        }
                    }){
                @Override
                public Map getHeaders() throws AuthFailureError {
                    return headers;
                }
            };
            //add request to queue to be sent to API
            requestQueue.add(jsonObjectRequest);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void getData(Boolean requiresAuth, JSONObject request, final Map headers, String location, final ServerCallback callback ){
        if (requiresAuth) {
            checkTokenExpiry();
        }
        RequestQueue requestQueue = RequestQueueSingleton.getInstance(context.getApplicationContext()).getRequestQueue();
        try {
            String url = context.getResources().getString(R.string.api_url) + location;
            JSONObject object = new JSONObject();
            JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(Request.Method.GET, url, request, new Response.Listener<JSONObject>() {
                @Override
                public void onResponse(JSONObject response) {
                    callback.onSuccess(response);
                }
            }, new Response.ErrorListener() {
                @Override
                public void onErrorResponse(VolleyError error) {
                    callback.onFail(error);
                }
            }){
                @Override
                public Map getHeaders() throws AuthFailureError {
                    return headers;
                }
            };
            requestQueue.add(jsonObjectRequest);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }


}
