package com.kalma.API_Interaction;

import com.android.volley.VolleyError;

import org.json.JSONObject;

//This interface is used to return API responses back to the caller when the API call has finished
public interface ServerCallback {
    void onSuccess(JSONObject result);
    void onFail(VolleyError error);
}