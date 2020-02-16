package com.kalma.Login;

import androidx.appcompat.app.AppCompatActivity;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;

import com.kalma.Login.LoginActivity;
import com.kalma.R;

public class StartPage extends AppCompatActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.app_start_page);

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
        //TODO Create login page
        //Intent intent = new Intent(this, LoginActivity.class);
        //startActivity(intent);
    }

}
