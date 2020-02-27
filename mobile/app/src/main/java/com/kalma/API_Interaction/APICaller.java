package com.kalma.API_Interaction;

import android.app.Activity;
import android.content.Context;
import android.content.res.Resources;
import android.provider.Settings;
import android.util.Log;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.google.gson.JsonIOException;
import com.kalma.Login.LoginActivity;
import com.kalma.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;

public class APICaller {
    //set application context to use getResource methods
    private Context context;
    public APICaller(Context context){
        this.context = context;
    }

    public void post(final JSONObject content, String location) {
        RequestQueue requestQueue = RequestQueueSingleton.getInstance(context.getApplicationContext()).getRequestQueue();
        String url = context.getResources().getString(R.string.api_url) + location;
        //create request
        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(Request.Method.POST, url, content,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {
                        try {
                            JSONObject responseBody = response;
                            String token  = responseBody.getString("access_token");
                            AuthStrings.getInstance(context).setAuthToken(token);
                            Log.d("Response", response.toString());
                        }
                        catch (JSONException je){}
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error ) {
                        try{
                            String jsonInput = new String(error.networkResponse.data, "utf-8");
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            AuthStrings.getInstance(context).setAuthToken(null);
                            Log.w("Error.Response", jsonInput);
                            Toast toast = Toast.makeText(context, message , Toast.LENGTH_LONG);
                            toast.show();
                        }
                        catch (JSONException je){
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                        catch (UnsupportedEncodingException err) {
                            Log.e("EncodingError", "onErrorResponse: ", err);
                        }
                    }

                }
        );
        //add request to queue to be sent to API
        requestQueue.add(jsonObjectRequest);
    }

    public void getData(JSONObject request, String location ){
        RequestQueue requestQueue = RequestQueueSingleton.getInstance(context.getApplicationContext()).getRequestQueue();
        try {
            String url = context.getResources().getString(R.string.api_url) + location;
            JSONObject object = new JSONObject();
            JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(Request.Method.GET, url, request, new Response.Listener<JSONObject>() {
                @Override
                public void onResponse(JSONObject response) {
                    //TODO deal with GET response
                }
            }, new Response.ErrorListener() {
                @Override
                public void onErrorResponse(VolleyError error) {
                    try{
                        String jsonInput = new String(error.networkResponse.data, "utf-8");
                        JSONObject responseBody = new JSONObject(jsonInput);
                        String message = responseBody.getString("message");
                        Log.w("Error.Response", jsonInput);
                        Toast toast = Toast.makeText(context, message , Toast.LENGTH_LONG);
                        toast.show();
                    }
                    catch (JSONException je){
                        Log.e("JSONException", "onErrorResponse: ", je);
                    }
                    catch (UnsupportedEncodingException err) {
                        Log.e("EncodingError", "onErrorResponse: ", err);
                    }
                }
            });
            requestQueue.add(jsonObjectRequest);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }


}
