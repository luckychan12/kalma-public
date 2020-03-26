package com.kalma.MainApp;

import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;

import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.Toast;

import com.android.volley.VolleyError;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.AuthStrings;
import com.kalma.API_Interaction.ServerCallback;
import com.kalma.Login.StartPage;
import com.kalma.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.util.HashMap;
import java.util.Map;

public class SettingsActivity extends AppCompatActivity {
    Context context = this;
    Button buttonHome, buttonProfile, buttonLogout;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_settings);
        buttonHome = findViewById(R.id.btnHome);
        buttonHome.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, HomeActivity.class);
                startActivity(intent);
            }
        });
        buttonProfile = findViewById(R.id.btnProfile);
        buttonProfile.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(context, UserProfileActivity.class);
                startActivity(intent);
            }
        });
        buttonLogout = findViewById(R.id.btnLogout);
        buttonLogout.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                AlertDialog.Builder builder = new AlertDialog.Builder(context);
                builder.setMessage("Are you sure you would like to logout?")
                        .setPositiveButton("Logout", new DialogInterface.OnClickListener() {
                            public void onClick(DialogInterface dialog, int id) {
                                logout();
                            }
                        })
                        .setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
                            public void onClick(DialogInterface dialog, int id) {
                                // User cancelled the dialog
                                // Do nothing
                            }
                        });
                // Create the AlertDialog object and return it
                final AlertDialog dialog = builder.create();
                dialog.setOnShowListener(new DialogInterface.OnShowListener() {
                    @Override
                    public void onShow(DialogInterface arg0) {
                        dialog.getButton(AlertDialog.BUTTON_NEGATIVE).setTextColor(getResources().getColor(R.color.cancelColour));
                        dialog.getButton(AlertDialog.BUTTON_POSITIVE).setTextColor(getResources().getColor(R.color.logoutColour));
                    }
                });
                dialog.show();
            }
        });
    }



    private Map buildMap() {
        Map<String, String> params = new HashMap<String, String>();
        params.put("Authorization", "Bearer " + AuthStrings.getInstance(getApplicationContext()).getAuthToken());
        return params;
    }

    private JSONObject logoutObject() {
        //TODO use input data instead of dummy data
        //returns a json object based on input email and password
        JSONObject object = new JSONObject();
        try {
            object.put("client_fingerprint", AuthStrings.getInstance(this).getDeviceToken());
        } catch (JSONException e) {
            e.printStackTrace();
        }
        return object;
    }

    public void logout() {
        //create a json object and call API to log in
        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.post(logoutObject(), buildMap(), AuthStrings.getInstance(getApplicationContext()).getLogoutLink(),new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        try {
                            String message = response.getString("message");
                            forgetTokens();
                            Toast toast = Toast.makeText(getApplicationContext(), message , Toast.LENGTH_LONG);
                            Log.d("Response", response.toString());
                            Intent intent = new Intent(context, StartPage.class);
                            startActivity(intent);
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                    }

                    @Override
                    public void onFail(VolleyError error) {
                        try{
                            forgetTokens();
                            //retrieve error message and display
                            String jsonInput = new String(error.networkResponse.data, "utf-8");
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            Log.w("Error.Response", jsonInput);
                            Toast toast = Toast.makeText(getApplicationContext(), message , Toast.LENGTH_LONG);
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

    }

    private void forgetTokens() {
        AuthStrings authStrings = AuthStrings.getInstance(getApplicationContext());
        authStrings.forgetAuthToken();
        authStrings.forgetRefreshToken();
    }


}


