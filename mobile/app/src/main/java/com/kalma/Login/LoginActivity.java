package com.kalma.Login;

import androidx.appcompat.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import com.android.volley.*;
import com.android.volley.toolbox.JsonObjectRequest;
import com.kalma.R;
import com.kalma.RequestQueueSingleton;
import android.provider.Settings.Secure;
import org.json.JSONException;
import org.json.JSONObject;

public class LoginActivity extends AppCompatActivity {
    EditText txtEmail, txtPassword;
    Button buttonLogin;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        txtEmail  = (EditText) findViewById(R.id.txtEmail);
        txtPassword = (EditText) findViewById(R.id.txtPassword);
        buttonLogin = (Button) findViewById(R.id.btnLogin);
        //buttonReset = (Button) findViewById(R.id.buttonReset);

        buttonLogin.setOnClickListener(new View.OnClickListener(){
            @Override
            public void onClick(View v){
                String email = txtEmail.getText().toString();
                String password = txtPassword.getText().toString();
                login(email, password);
            }
        });
    }


    public void login(String email, String password) {
        RequestQueue requestQueue = RequestQueueSingleton.getInstance(getApplicationContext()).getRequestQueue();
        JSONObject object = new JSONObject();
        try {
            object.put("email_address", "dummy@example.com");
            object.put("password","Password!123");
            object.put("client_fingerprint", Long.parseLong(Secure.getString(LoginActivity.this.getContentResolver(), Secure.ANDROID_ID),16));
        } catch (JSONException e) {
            e.printStackTrace();
        }
        String url = getResources().getString(R.string.api_url) + getResources().getString(R.string.api_login);
        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(Request.Method.POST, url, object,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {
                        Log.d("Response", response.toString());
                    }
                }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {
                Log.w("Error.Response", error.toString());
            }
        });
        requestQueue.add(jsonObjectRequest);
    }
}

