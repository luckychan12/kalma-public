package com.kalma.Login;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;

import com.android.volley.VolleyError;
import com.kalma.API_Interaction.APICaller;
import com.kalma.API_Interaction.ServerCallback;
import com.kalma.MainApp.HomeActivity;
import com.kalma.R;

import org.joda.time.DateTimeZone;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;
import java.util.TimeZone;

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
        return new HashMap<String, String>();
    }

    private void onSuccessfulLogin() {
        Intent intent = new Intent(this, HomeActivity.class);
        startActivity(intent);
    }
    private void attemptLoginRefresh() {
        ServerCallback refreshLoginCallback = new ServerCallback() {
            @Override
            public void onSuccess(JSONObject result) {
                onSuccessfulLogin();
            }
            @Override
            public void onFail(VolleyError error) {
                //do nothing
            }
        };
        //attempt to refresh
        APICaller apiCaller = new APICaller(getApplicationContext());
        apiCaller.loginRefresh(refreshLoginCallback);
    }
}
