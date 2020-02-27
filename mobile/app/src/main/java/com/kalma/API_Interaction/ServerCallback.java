package com.kalma.API_Interaction;

import com.android.volley.VolleyError;

import org.json.JSONObject;

public interface ServerCallback {
    void onSuccess(JSONObject result);

    void onFail(VolleyError error);
}