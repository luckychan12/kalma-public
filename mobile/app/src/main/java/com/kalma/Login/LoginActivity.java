package com.kalma.Login;

import android.content.Intent;
import android.os.Bundle;
import android.provider.Settings.Secure;
import android.util.Log;
import android.view.View;
import android.widget.Button;
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

//TODO Implement error handling and data validation

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


    public void login(String email, String password) {
        //create a json object and call API to log in
        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.post(buildLoginJsonObject(email, password), getResources().getString(R.string.api_login), new ServerCallback() {
                    @Override
                    public void onSuccess(JSONObject response) {
                        try {
                            JSONObject responseBody = response;
                            String token = responseBody.getString("access_token");
                            AuthStrings.getInstance(getApplicationContext()).setAuthToken(token);
                            Log.d("Response", response.toString());
                        } catch (JSONException je) {
                            Log.e("JSONException", "onErrorResponse: ", je);
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
            object.put("email_address", "dummy@example.com");
            object.put("password", "Password123!");
            object.put("client_fingerprint", AuthStrings.getInstance(this).getDeviceToken());
        } catch (JSONException e) {
            e.printStackTrace();
        }
        return object;
    }
}

