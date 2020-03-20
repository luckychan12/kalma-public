package com.kalma.MainApp;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.VolleyError;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.AuthStrings;
import com.kalma.API_Interaction.ServerCallback;
import com.kalma.R;

import org.joda.time.DateTime;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.util.HashMap;
import java.util.Map;

public class UserProfileActivity extends AppCompatActivity {
    Context context = this;
    Button buttonHome, buttonSettings;
    TextView txtUserID, txtEmail, txtFName, txtLName, txtDoB;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_user_profile);
        getData();
        buttonHome = findViewById(R.id.btnHome);
        buttonHome.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, HomeActivity.class);
                startActivity(intent);
            }
        });
        buttonSettings = findViewById(R.id.btnSettings);
        buttonSettings.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, SettingsActivity.class);
                startActivity(intent);
            }
        });
    }
    private void fillData(JSONObject userBody){
        txtUserID = findViewById(R.id.UserID);
        txtEmail = findViewById(R.id.Email);
        txtFName = findViewById(R.id.FirstName);
        txtLName = findViewById(R.id.LastName);
        txtDoB = findViewById(R.id.DoBlbl);
        try {
            txtUserID.setText(userBody.getString("user_id"));
            txtEmail.setText(userBody.getString("email_address"));
            txtFName.setText(userBody.getString("first_name"));
            txtLName.setText(userBody.getString("last_name"));
            String epochDoB = userBody.getString("date_of_birth");
            DateTime dobTime = new DateTime(epochDoB);
            txtDoB.setText(dobTime.toString("dd/MM/yyyy"));
        } catch (JSONException je) {
            Log.e("JSONException", "onErrorResponse: ", je);
        }

    }

    private void getData() {
        //create a json object and call API to log in
        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.getData(null,buildMap(),AuthStrings.getInstance(getApplicationContext()).getAccountLink(), new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        try {
                            JSONObject userBody = response.getJSONObject("user");
                            fillData(userBody);
                            Log.d("Response", response.toString());
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
                            Log.e("Error.Response", responseBody.toString());
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



    private Map buildMap() {
        Map<String, String> params = new HashMap<String, String>();
        params.put("Authorization", "Bearer " + AuthStrings.getInstance(getApplicationContext()).getAuthToken());
        return params;
    }
}