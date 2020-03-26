package com.kalma.Login;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.Toast;

import com.android.volley.VolleyError;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.AuthStrings;
import com.kalma.API_Interaction.ServerCallback;
import com.kalma.Login.LoginActivity;
import com.kalma.MainApp.HomeActivity;
import com.kalma.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.util.HashMap;
import java.util.Map;

public class StartPage extends AppCompatActivity {
    public static final String MyPREFERENCES = "TOKENS";
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.app_start_page);
        attemptLoginRefresh();
        Button loginButton = findViewById(R.id.btnLogin);
        loginButton.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View v){

                openLoginPage();
            }
        });

        Button signUpButton = findViewById(R.id.btnSignUp);
        signUpButton.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View v){
                openSignUpPage();
            }
        });
    }

    private void openLoginPage(){
        Intent intent = new Intent(this, LoginActivity.class);
        startActivity(intent);
    }

    private void openSignUpPage(){
        Intent intent = new Intent(this, SignUpActivity.class);
        startActivity(intent);
    }

    @Override
    public void onBackPressed() {
        //Do nothing
        //Prevents user from returning after logout
    }

    private Map buildMap() {
        Map<String, String> params = new HashMap<String, String>();
        return params;
    }

    private void onSuccessfulLogin() {
        Intent intent = new Intent(this, HomeActivity.class);
        startActivity(intent);
    }

    private void attemptLoginRefresh() {
        SharedPreferences settings = getApplicationContext().getSharedPreferences(MyPREFERENCES, 0);
        String token = settings.getString("RefreshToken", "");
        System.out.println("TOKEN =    \"" + token + "\"");
        if (token == ""){
            return;
        }
        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.post(buildRefreshObject(token), buildMap(), getResources().getString(R.string.api_refresh), new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject result) {
                        try {
                            JSONObject responseBody = result;
                            String accessToken = responseBody.getString("access_token");
                            int accessExp = Integer.parseInt(responseBody.getString("access_expiry"));
                            AuthStrings.getInstance(getApplicationContext()).setAuthToken(accessToken, accessExp);
                            String refreshToken = responseBody.getString("refresh_token");
                            int refreshExp = Integer.parseInt(responseBody.getString("refresh_expiry"));
                            AuthStrings.getInstance(getApplicationContext()).setRefreshToken(refreshToken, refreshExp);
                            onSuccessfulLogin();
                        }
                        catch (JSONException je) {
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
                            AuthStrings.getInstance(getApplicationContext()).forgetRefreshToken();
                            Log.w("Error.Response", jsonInput);
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        } catch (UnsupportedEncodingException err) {
                            Log.e("EncodingError", "onErrorResponse: ", err);
                        }
                    }

                }
        );
    }

    private JSONObject buildRefreshObject(String token) {
        //returns a json object based on input email and password
        JSONObject object = new JSONObject();
        try {
            object.put("refresh_token", token);
            object.put("client_fingerprint", AuthStrings.getInstance(getApplicationContext()).getDeviceToken());
        } catch (JSONException e) {
            e.printStackTrace();
        }
        return object;
    }

}
