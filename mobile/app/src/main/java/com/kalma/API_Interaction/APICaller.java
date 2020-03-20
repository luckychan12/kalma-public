package com.kalma.API_Interaction;

import android.content.Context;

import com.android.volley.AuthFailureError;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.kalma.R;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;


public class APICaller {
    //set application context to use getResource methods
    private Context context;
    public APICaller(Context context){
        this.context = context;
    }
    public void post(final JSONObject content, final Map headers, String location, final ServerCallback callback) {
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
            public Map<String, String> getHeaders() throws AuthFailureError {
                return headers;
            }
        };
        //add request to queue to be sent to API
        requestQueue.add(jsonObjectRequest);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void getData(JSONObject request, final Map headers, String location, final ServerCallback callback ){
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
                public Map<String, String> getHeaders() throws AuthFailureError {
                    return headers;
                }
            };
            requestQueue.add(jsonObjectRequest);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }


}
