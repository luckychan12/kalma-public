package com.kalma.Login;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.VolleyError;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.AuthStrings;
import com.kalma.API_Interaction.ServerCallback;
import com.kalma.MainApp.HomeActivity;
import com.kalma.R;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.UnsupportedEncodingException;
import java.nio.charset.StandardCharsets;
import java.util.HashMap;
import java.util.Map;

public class LoginActivity extends AppCompatActivity {
    EditText txtEmail, txtPassword;
    Button buttonLogin;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);
        //get view element resources
        txtEmail = findViewById(R.id.txtEmail);
        txtPassword = findViewById(R.id.txtPassword);
        buttonLogin = findViewById(R.id.btnLogin);
        //buttonReset = (Button) findViewById(R.id.buttonReset);

        buttonLogin.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                String email = txtEmail.getText().toString();
                String password = txtPassword.getText().toString();
                login(email, password);
            }
        });


    }

    private void onSuccessfulLogin() {
        Intent intent = new Intent(this, HomeActivity.class);
        startActivity(intent);
    }

    private Map buildMap() {
        Map<String, String> params = new HashMap<String, String>();
        return params;
    }

    public void login(String email, String password) {
        //create a json object and call API to log in
        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.post(false, buildLoginJsonObject(email, password), buildMap(), getResources().getString(R.string.api_login), new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        try {
                            //retrieve access token and store.
                            JSONObject responseBody = response;
                            String accessToken = responseBody.getString("access_token");
                            int accessExp = Integer.parseInt(responseBody.getString("access_expiry"));
                            int refreshExp = Integer.parseInt(responseBody.getString("refresh_expiry"));
                            JSONObject links = responseBody.getJSONObject("links");
                            String accLink = links.getString("account");
                            String logoutLink = links.getString("logout");
                            String refreshToken = responseBody.getString("refresh_token");
                            StoreTokens(accessToken, accessExp, refreshExp, accLink, logoutLink, refreshToken);
                            Log.d("Response", response.toString());
                            //open home page
                            onSuccessfulLogin();
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
                        }
                    }

                    private void StoreTokens(String accessToken, int accessExp, int refreshExp, String accLink, String logoutLink, String refreshToken) {
                        AuthStrings authStrings = AuthStrings.getInstance(getApplicationContext());
                        authStrings.setAuthToken(accessToken, accessExp);
                        authStrings.setAccountLink(accLink);
                        authStrings.setLogoutLink(logoutLink);
                        authStrings.setRefreshToken(refreshToken, refreshExp);
                        if (((CheckBox)findViewById(R.id.rememberCreds)).isChecked()){
                            authStrings.storeRefreshToken();
                        }
                        else{
                            authStrings.unstoreRefreshToken();
                        }
                    }

                    @Override
                    public void onFail(VolleyError error) {
                        try {
                            //retrieve error message and display
                            String jsonInput = new String(error.networkResponse.data, "utf-8");
                            JSONObject responseBody = new JSONObject(jsonInput);
                            String message = responseBody.getString("message");
                            AuthStrings.getInstance(getApplicationContext()).setAuthToken(null, 0);
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

    private JSONObject buildLoginJsonObject(String email, String password) {
        //TODO use input data instead of dummy data
        //returns a json object based on input email and password
        JSONObject object = new JSONObject();
        try {
            object.put("email_address", email);
            object.put("password", password);
            object.put("client_fingerprint", AuthStrings.getInstance(this).getDeviceToken());
        } catch (JSONException e) {
            e.printStackTrace();
        }
        return object;
    }
}

