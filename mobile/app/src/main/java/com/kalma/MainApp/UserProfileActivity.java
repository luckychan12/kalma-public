package com.kalma.MainApp;

import androidx.appcompat.app.AppCompatActivity;

import android.os.Bundle;
import android.util.Log;
import android.widget.Toast;

import com.android.volley.VolleyError;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.AuthStrings;
import com.kalma.API_Interaction.ServerCallback;
import com.kalma.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;

public class UserProfile extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_user_profile);
    }

    public void getData() {
        //create a json object and call API to log in
        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.getData(buildJsonObject(),AuthStrings.getInstance(getApplicationContext()).getAccountLink(), new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        try {
                            //retrieve access token and store.
                            JSONObject responseBody = response;
                            String accessToken = responseBody.getString("access_token");
                            Log.d("Response", response.toString());
                            //open home page
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                    }

                    @Override
                    public void onFail(VolleyError error) {
                        try {
                            //retrieve error message and display
                            String jsonInput = new String(error.networkResponse.data, "utf-8");
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            Log.w("Error.Response", jsonInput);
                            Toast toast = Toast.makeText(getApplicationContext(), message, Toast.LENGTH_LONG);
                            toast.show();
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        } catch (UnsupportedEncodingException err) {
                            Log.e("EncodingError", "onErrorResponse: ", err);
                        }
                    }
                }
        );

    }



    private JSONObject buildJsonObject() {
        //TODO use input data instead of dummy data
        //returns a json object based on input email and password
        JSONObject object = new JSONObject();
        try {
            object.put("Authorization", AuthStrings.getInstance(getApplicationContext()).getAuthToken());
        } catch (JSONException e) {
            e.printStackTrace();
        }
        return object;
    }
}