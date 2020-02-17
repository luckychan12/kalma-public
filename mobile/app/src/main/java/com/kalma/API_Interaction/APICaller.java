package com.kalma.API_Interaction;

import android.content.Context;
import android.content.res.Resources;
import android.provider.Settings;
import android.util.Log;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import com.kalma.Login.LoginActivity;
import com.kalma.R;

import org.json.JSONException;
import org.json.JSONObject;

public class APICaller {
    //set application context to use getResource methods
    private Context context;
    public APICaller(Context context){
        this.context = context;
    }

    public void post(JSONObject content, String location) {
        RequestQueue requestQueue = RequestQueueSingleton.getInstance(context.getApplicationContext()).getRequestQueue();
        String url = context.getResources().getString(R.string.api_url) + location;
        //create request
        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(Request.Method.POST, url, content,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {
                        //TODO handle API response and return JSON response
                        Log.d("Response", response.toString());
                    }
                }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {
                Log.w("Error.Response", error.toString());
            }
        });
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
                    Log.w("Error.Response", error.toString());
                }
            });
            requestQueue.add(jsonObjectRequest);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }


}
