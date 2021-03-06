package com.kalma.API_Interaction;

import android.content.Context;
import android.content.SharedPreferences;
import android.util.Log;

import com.android.volley.AuthFailureError;
import com.android.volley.DefaultRetryPolicy;
import com.android.volley.NetworkResponse;
import com.android.volley.ParseError;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.HttpHeaderParser;
import com.android.volley.toolbox.JsonObjectRequest;

import com.kalma.Data.AuthStrings;
import com.kalma.R;

import org.joda.time.DateTime;
import org.joda.time.format.DateTimeFormatter;
import org.joda.time.format.ISODateTimeFormat;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.nio.charset.StandardCharsets;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Hashtable;
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
        if (token.equals("")){
            return;
        }
        refreshTokens(token, callback);
    }

    private void checkTokenExpiry(){
        DateTime now = new DateTime();
        String token = AuthStrings.getInstance(context).getRefreshToken();
        if(AuthStrings.getInstance(context).getAuthExp().isAfter(now)){
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
                                String accessToken = result.getString("access_token");
                                DateTimeFormatter parser = ISODateTimeFormat.dateTimeParser();
                                DateTime accessExp = parser.parseDateTime(result.getString("access_expiry"));
                                authStrings.setAuthToken(accessToken, accessExp);
                                String refreshToken = result.getString("refresh_token");
                                DateTime refreshExp = parser.parseDateTime(result.getString("refresh_expiry"));
                                authStrings.setRefreshToken(refreshToken, refreshExp);
                                JSONObject links = result.getJSONObject("links");
                                Hashtable<String, String> linksDict = new Hashtable<String, String>(); ;
                                linksDict.put("account", links.getString("account"));
                                linksDict.put("logout", links.getString("logout"));
                                linksDict.put("sleep", links.getString("sleep"));
                                linksDict.put("calm", links.getString("calm"));
                                linksDict.put("steps", links.getString("steps"));
                                linksDict.put("height", links.getString("height"));
                                linksDict.put("weight", links.getString("weight"));

                                authStrings.setLinks(linksDict);
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
                                String jsonInput = new String(error.networkResponse.data, StandardCharsets.UTF_8);
                                JSONObject responseBody = new JSONObject(jsonInput);
                                String message = responseBody.getString("message");
                                AuthStrings.getInstance(context).forgetRefreshToken();
                                Log.w("Error.Response", jsonInput);

                            } catch (JSONException je) {
                                Log.e("JSONException", "onErrorResponse: ", je);
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

                @Override
                protected Response<JSONObject> parseNetworkResponse(NetworkResponse response) {
                    try {
                        Log.i("STATUS CODE", Integer.toString(response.statusCode));
                        Log.i("RESPONSE LENGTH", Integer.toString(response.data.length));
                        String str = new String(response.data);
                        Log.i("RESPONSE", str);
                        Log.i("RESPONSE2", Arrays.toString(response.data));

                        String jsonString = new String(response.data,
                                HttpHeaderParser.parseCharset(response.headers, PROTOCOL_CHARSET));

                        JSONObject result = null;

                        if (jsonString.length() > 0)
                            result = new JSONObject(jsonString);

                        return Response.success(result,
                                HttpHeaderParser.parseCacheHeaders(response));
                    } catch (UnsupportedEncodingException | JSONException e) {
                        return Response.error(new ParseError(e));
                    }
                }
            };
            jsonObjectRequest.setRetryPolicy(new DefaultRetryPolicy(
                    DefaultRetryPolicy.DEFAULT_TIMEOUT_MS,
                    DefaultRetryPolicy.DEFAULT_MAX_RETRIES,
                    DefaultRetryPolicy.DEFAULT_BACKOFF_MULT));
            requestQueue.add(jsonObjectRequest);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
